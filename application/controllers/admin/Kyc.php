<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Admin panel: KYC Document Verification. Ports
 * application/controllers/api/v1/Kyc_document.php's store()/verify()/
 * download() -- same upload constraints (5MB, jpg/png/gif/webp/pdf), same
 * required rejection reason, same gated inline file view. Aadhaar/PAN
 * verification logs have no approval workflow of their own (see
 * Kyc_aadhaar.php/Kyc_pan.php) so they're surfaced read-only on
 * views/admin/customer_show.php instead of here.
 */
class Kyc extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->require_admin_role(array('BRANCH_EXECUTIVE', 'BRANCH_MANAGER', 'REGIONAL_MANAGER'));

        $this->load->model('Customer_model', 'customers');
        $this->load->model('Kyc_document_type_model', 'document_types');
        $this->load->model('Kyc_document_model', 'documents');
    }

    /** GET /admin/kyc */
    public function index()
    {
        $status = trim((string) $this->input->get('status')) ?: 'PENDING';
        $search = trim((string) $this->input->get('search'));
        $page = max(1, (int) $this->input->get('page'));

        $result = $this->documents->with_relations($status !== 'ALL' ? $status : null, $search, 15, $page);

        $this->render('kyc', array(
            'page_title' => 'KYC Verification',
            'status' => $status,
            'documents' => $result['data'],
            'pagination' => $result,
            'filters' => array('status' => $status, 'search' => $search),
            'document_types' => $this->document_types->all(array(), 'name ASC'),
            'can_upload' => in_array($this->user['role_code'], array('BRANCH_EXECUTIVE', 'BRANCH_MANAGER'), true),
            'can_verify' => in_array($this->user['role_code'], array('BRANCH_MANAGER', 'REGIONAL_MANAGER'), true),
        ));
    }

    /** POST /admin/kyc/upload -- role BRANCH_EXECUTIVE/BRANCH_MANAGER */
    public function upload()
    {
        if (! $this->require_admin_role(array('BRANCH_EXECUTIVE', 'BRANCH_MANAGER'))) {
            return;
        }

        $customer_mobile = trim((string) $this->input->post('customer_mobile'));
        $customer = $customer_mobile !== '' ? $this->customers->find_by_mobile($customer_mobile) : null;
        if (! $customer) {
            return $this->_fail('No customer found with that mobile number.');
        }

        $document_type_id = $this->input->post('document_type_id');
        if (! $document_type_id || ! $this->document_types->find($document_type_id)) {
            return $this->_fail('A valid document type is required.');
        }

        if (empty($_FILES['file']) || empty($_FILES['file']['name'])) {
            return $this->_fail('A file is required.');
        }
        if ($_FILES['file']['size'] > 5120 * 1024) {
            return $this->_fail('File must not be greater than 5120 kilobytes.');
        }

        $upload_dir = FCPATH . 'uploads/kyc-documents';
        if (! is_dir($upload_dir)) {
            mkdir($upload_dir, 0775, true);
        }

        $this->load->library('upload', array(
            'upload_path' => $upload_dir,
            'allowed_types' => 'jpg|jpeg|png|gif|webp|pdf',
            'max_size' => 5120,
            'encrypt_name' => true,
        ));

        if (! $this->upload->do_upload('file')) {
            return $this->_fail($this->upload->display_errors('', ''));
        }

        $uploaded = $this->upload->data();
        $file_ref = 'kyc-documents/' . $uploaded['file_name'];

        $document_id = $this->documents->insert(array(
            'customer_id' => $customer['id'],
            'document_type_id' => $document_type_id,
            'file_ref' => $file_ref,
            'status' => 'PENDING',
        ));

        $this->audit_log('KycDocument', $document_id, 'KYC_DOCUMENT_UPLOAD', null, $this->documents->find($document_id));

        $this->session->set_flashdata('status', 'KYC document uploaded for ' . htmlspecialchars($customer['name']) . '.');
        redirect('admin/kyc');
    }

    /** POST /admin/kyc/(:num)/verify -- role BRANCH_MANAGER/REGIONAL_MANAGER */
    public function verify($id)
    {
        if (! $this->require_admin_role(array('BRANCH_MANAGER', 'REGIONAL_MANAGER'))) {
            return;
        }

        $document = $this->documents->find($id);
        if (! $document) {
            show_404();

            return;
        }

        $decision = $this->input->post('decision');
        if (! in_array($decision, array('VERIFIED', 'REJECTED'), true)) {
            return $this->_fail('A decision (verify or reject) is required.');
        }

        $reason = trim((string) $this->input->post('reason'));
        if ($decision === 'REJECTED' && $reason === '') {
            return $this->_fail('A reason is required when rejecting a KYC document.');
        }

        $this->documents->update($id, array(
            'status' => $decision,
            'verified_by' => $this->user['id'],
            'rejection_reason' => $decision === 'REJECTED' ? $reason : null,
        ));

        $action = $decision === 'VERIFIED' ? 'KYC_DOCUMENT_VERIFY' : 'KYC_DOCUMENT_REJECT';
        $this->audit_log('KycDocument', $id, $action, $document, $this->documents->find($id));

        $this->session->set_flashdata('status', 'KYC document ' . strtolower($decision) . '.');
        redirect('admin/kyc');
    }

    /** GET /admin/kyc/(:num)/file -- gated inline file view */
    public function file($id)
    {
        $document = $this->documents->find($id);
        if (! $document) {
            show_404();

            return;
        }

        $path = FCPATH . 'uploads/' . $document['file_ref'];
        if (! is_file($path)) {
            show_404();

            return;
        }

        $this->audit_log('KycDocument', $id, 'KYC_DOCUMENT_VIEW', null, null);

        $mime = function_exists('mime_content_type') ? mime_content_type($path) : 'application/octet-stream';
        header('Content-Type: ' . $mime);
        header('Content-Disposition: inline; filename="' . basename($path) . '"');
        header('Content-Length: ' . filesize($path));
        readfile($path);
        exit;
    }

    private function _fail($message)
    {
        $this->session->set_flashdata('error', $message);
        redirect('admin/kyc');
    }
}
