<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Mirrors app/Http/Controllers/Api/V1/KycDocumentController.php. Same route
 * group as the rest of KYC (auth:sanctum + device.binding, no role
 * restriction on any of these three routes in routes/api.php).
 */
class Kyc_document extends Api_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Customer_model', 'customers');
        $this->load->model('Kyc_document_type_model', 'document_types');
        $this->load->model('Kyc_document_model', 'documents');
    }

    /** POST /api/v1/kyc/document */
    public function store()
    {
        $this->require_auth();
        $this->require_device_binding();

        $data = $this->json_input();

        if (empty($data['customer_id']) || ! $this->customers->find($data['customer_id'])) {
            return json_error('customer_id is required and must reference an existing customer.');
        }
        if (empty($data['document_type_id']) || ! $this->document_types->find($data['document_type_id'])) {
            return json_error('document_type_id is required and must reference an existing document type.');
        }

        // Mirrors Laravel's multipart `file` upload (max 5120 KB), stored via
        // Storage::store('kyc-documents') — same local-disk pattern as
        // Jewellery::upload_image(), since the Laravel app's default
        // filesystem disk is local ('uploads'), not S3.
        if (empty($_FILES['file']) || empty($_FILES['file']['name'])) {
            return json_error('file is required.');
        }
        if ($_FILES['file']['size'] > 5120 * 1024) {
            return json_error('file must not be greater than 5120 kilobytes.');
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
            return json_error($this->upload->display_errors('', ''));
        }

        $uploaded = $this->upload->data();
        $file_ref = 'kyc-documents/' . $uploaded['file_name'];

        $document_id = $this->documents->insert(array(
            'customer_id' => $data['customer_id'],
            'document_type_id' => $data['document_type_id'],
            'file_ref' => $file_ref,
            'status' => 'PENDING',
        ));

        $document = $this->documents->find($document_id);

        $this->audit_log('KycDocument', $document_id, 'KYC_DOCUMENT_UPLOAD', null, $document);

        return json_response(array('data' => $document), 201);
    }

    /** GET /api/v1/kyc/document-types */
    public function document_types_index()
    {
        $this->require_auth();
        $this->require_device_binding();

        return json_response(array(
            'data' => $this->document_types->all(array(), 'name ASC'),
        ));
    }

    /** GET /api/v1/kyc/document/{customerId} */
    public function index($customer_id)
    {
        $this->require_auth();
        $this->require_device_binding();

        return json_response(array('data' => $this->documents->for_customer($customer_id)));
    }

    /** PUT /api/v1/kyc/document/{id}/verify */
    public function verify($id)
    {
        $user = $this->require_auth();
        $this->require_device_binding();

        $document = $this->documents->find($id);
        if (! $document) {
            return json_error('KYC document not found.', 404);
        }

        $data = $this->json_input();

        if (empty($data['status']) || ! in_array($data['status'], array('VERIFIED', 'REJECTED'), true)) {
            return json_error('status is required and must be VERIFIED or REJECTED.');
        }

        $this->documents->update($id, array(
            'status' => $data['status'],
            'verified_by' => $user['id'],
        ));

        $updated = $this->documents->find($id);

        $action = $data['status'] === 'VERIFIED' ? 'KYC_DOCUMENT_VERIFY' : 'KYC_DOCUMENT_REJECT';
        $this->audit_log('KycDocument', $id, $action, $document, $updated);

        return json_response(array('data' => $updated));
    }
}
