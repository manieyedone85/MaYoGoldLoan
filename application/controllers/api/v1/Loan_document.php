<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Not a Laravel port -- added for BRD §9 "Loan agreement & documents stored"
 * (docs/BRD_COVERAGE_AUDIT.md): no loan-document/agreement model or
 * controller existed anywhere in the codebase. Mirrors the upload/list/
 * gated-download shape of Kyc_document.php (application/controllers/api/v1/
 * Kyc_document.php), which is the closest existing analog in this codebase.
 */
class Loan_document extends Api_Controller
{
    const DOCUMENT_TYPES = array('AGREEMENT', 'SANCTION_LETTER', 'OTHER');

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Loan_model', 'loans');
        $this->load->model('Loan_document_model', 'documents');
    }

    /** POST /api/v1/loan/{id}/document */
    public function store($loan_id)
    {
        $user = $this->require_auth();
        $this->require_device_binding();
        $this->require_role(array('BRANCH_EXECUTIVE', 'BRANCH_MANAGER', 'ADMIN'));

        $loan = $this->loans->find($loan_id);
        if (! $loan) {
            return json_error('Loan not found.', 404);
        }

        $data = $this->json_input();
        $document_type = ! empty($data['document_type']) ? $data['document_type'] : 'AGREEMENT';
        if (! in_array($document_type, self::DOCUMENT_TYPES, true)) {
            return json_error('document_type must be one of ' . implode(',', self::DOCUMENT_TYPES) . '.');
        }

        if (empty($_FILES['file']) || empty($_FILES['file']['name'])) {
            return json_error('file is required.');
        }
        if ($_FILES['file']['size'] > 5120 * 1024) {
            return json_error('file must not be greater than 5120 kilobytes.');
        }

        $upload_dir = FCPATH . 'uploads/loan-documents';
        if (! is_dir($upload_dir)) {
            mkdir($upload_dir, 0775, true);
        }

        $this->load->library('upload', array(
            'upload_path' => $upload_dir,
            'allowed_types' => 'pdf|jpg|jpeg|png',
            'max_size' => 5120,
            'encrypt_name' => true,
        ));

        if (! $this->upload->do_upload('file')) {
            return json_error($this->upload->display_errors('', ''));
        }

        $uploaded = $this->upload->data();
        $file_ref = 'loan-documents/' . $uploaded['file_name'];

        $document_id = $this->documents->insert(array(
            'loan_id' => $loan_id,
            'document_type' => $document_type,
            'file_ref' => $file_ref,
            'uploaded_by' => $user['id'],
        ));

        $document = $this->documents->find($document_id);

        $this->audit_log('Loan', $loan_id, 'LOAN_DOCUMENT_UPLOAD', null, $document);

        return json_response(array('data' => $document), 201);
    }

    /** GET /api/v1/loan/{id}/document */
    public function index($loan_id)
    {
        $this->require_auth();
        $this->require_device_binding();

        $loan = $this->loans->find($loan_id);
        if (! $loan) {
            return json_error('Loan not found.', 404);
        }

        return json_response(array('data' => $this->documents->for_loan($loan_id)));
    }

    /**
     * GET /api/v1/loan/document/{id}/file
     * Gated file-serving endpoint -- same rationale as
     * Kyc_document::download(): a document shouldn't be reachable by just
     * guessing the obfuscated filename under uploads/.
     */
    public function download($id)
    {
        $this->require_auth();
        $this->require_device_binding();
        $this->require_role(array('BRANCH_EXECUTIVE', 'BRANCH_MANAGER', 'REGIONAL_MANAGER', 'OPERATIONS', 'ADMIN'));

        $document = $this->documents->find($id);
        if (! $document) {
            return json_error('Loan document not found.', 404);
        }

        $path = FCPATH . 'uploads/' . $document['file_ref'];
        if (! is_file($path)) {
            return json_error('File not found.', 404);
        }

        $this->audit_log('Loan', $document['loan_id'], 'LOAN_DOCUMENT_VIEW', null, null);

        $mime = function_exists('mime_content_type') ? mime_content_type($path) : 'application/octet-stream';
        header('Content-Type: ' . $mime);
        header('Content-Disposition: inline; filename="' . basename($path) . '"');
        header('Content-Length: ' . filesize($path));
        readfile($path);
        exit;
    }
}
