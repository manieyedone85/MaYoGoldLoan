<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Admin panel: Customers.
 * Port of App\Http\Livewire\Admin\Customers\Index.
 *
 * Livewire's "view" modal becomes its own page (GET /admin/customers/(:num))
 * since that's how routes.php already wires it. See routes.php:
 * admin/customers (index), admin/customers/(:num) (show),
 * admin/customers/(:num)/blacklist (toggle_blacklist).
 */
class Customers extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Customer_model', 'customers');
        $this->load->model('Loan_model', 'loans');
        $this->load->model('Kyc_document_model', 'kyc_documents');
        $this->load->model('Kyc_aadhaar_verification_model', 'aadhaar_verifications');
        $this->load->model('Kyc_pan_verification_model', 'pan_verifications');
        $this->load->model('Customer_nominee_model', 'nominees');
    }

    /** GET /admin/customers */
    public function index()
    {
        $search = trim((string) $this->input->get('search'));
        $kyc_status = trim((string) $this->input->get('kyc_status'));
        $page = max(1, (int) $this->input->get('page'));

        $result = $this->customers->admin_list($search, $kyc_status, 10, $page);

        $this->render('customers', array(
            'page_title' => 'Customers',
            'customers' => $result['data'],
            'pagination' => $result,
            'filters' => array('search' => $search, 'kyc_status' => $kyc_status),
        ));
    }

    /** GET /admin/customers/(:num) */
    public function show($id)
    {
        $customer = $this->customers->with_branch($id);

        if (! $customer) {
            show_404();

            return;
        }

        $loans = $this->loans->with_relations(array('loans.customer_id' => $id));

        $this->render('customer_show', array(
            'page_title' => $customer['name'] . ' (' . $customer['customer_code'] . ')',
            'customer' => $customer,
            'loans' => $loans,
            'kyc_documents' => $this->kyc_documents->for_customer($id),
            'aadhaar_verifications' => $this->aadhaar_verifications->for_customer($id),
            'pan_verifications' => $this->pan_verifications->for_customer($id),
            'nominees' => $this->nominees->for_customer($id),
        ));
    }

    /** POST /admin/customers/(:num)/blacklist -- restricted: blacklisting affects a customer's ability to borrow anywhere, not a front-desk action. */
    public function toggle_blacklist($id)
    {
        if (! $this->require_admin_role(array('SUPER_ADMIN','ADMIN', 'BRANCH_MANAGER', 'REGIONAL_MANAGER', 'OPERATIONS'))) {
            return;
        }

        $customer = $this->customers->find($id);

        if (! $customer) {
            show_404();

            return;
        }

        $this->customers->update($id, array('is_blacklisted' => $customer['is_blacklisted'] ? 0 : 1));

        $this->session->set_flashdata('status', 'Customer blacklist status updated.');
        redirect('admin/customers');
    }

    /**
     * POST /admin/customers/(:num)/aadhaar-verify -- port of
     * Api\V1\KycAadhaarController::qrScan() (application/controllers/api/v1/Kyc_aadhaar.php).
     * Same privacy rule: the full Aadhaar number is never persisted, only
     * aadhaar_last4 + a SHA-256 hash on the customer row.
     */
    public function verify_aadhaar($id)
    {
        $customer = $this->customers->find($id);
        if (! $customer) {
            show_404();

            return;
        }

        $aadhaar_number = trim((string) $this->input->post('aadhaar_number'));
        if (! preg_match('/^\d{12}$/', $aadhaar_number)) {
            return $this->_kyc_fail($id, 'Aadhaar number is required and must be exactly 12 digits.');
        }

        $before = array(
            'aadhaar_last4' => $customer['aadhaar_last4'],
            'aadhaar_hash' => $customer['aadhaar_hash'],
        );

        $this->customers->update($customer['id'], array(
            'aadhaar_last4' => substr($aadhaar_number, -4),
            'aadhaar_hash' => hash('sha256', $aadhaar_number),
        ));

        $uidai_reference_id = trim((string) $this->input->post('uidai_reference_id'));

        $verification_id = $this->aadhaar_verifications->insert(array(
            'customer_id' => $customer['id'],
            'method' => 'QR',
            'uidai_reference_id' => $uidai_reference_id !== '' ? $uidai_reference_id : null,
            'is_verified' => 1,
            'verified_at' => date('Y-m-d H:i:s'),
        ));

        // Never log the raw aadhaar_number -- only masked/hashed values are persisted anyway.
        $after = array(
            'aadhaar_last4' => substr($aadhaar_number, -4),
            'aadhaar_hash' => hash('sha256', $aadhaar_number),
            'verification' => $this->aadhaar_verifications->find($verification_id),
        );
        $this->audit_log('Customer', $customer['id'], 'KYC_AADHAAR_QR_SCAN', $before, $after);

        $this->session->set_flashdata('status', 'Aadhaar verification recorded for ' . htmlspecialchars($customer['name']) . '.');
        redirect('admin/customers/' . $id);
    }

    /**
     * POST /admin/customers/(:num)/pan-verify -- port of
     * Api\V1\KycPanController::validatePan() (application/controllers/api/v1/Kyc_pan.php).
     */
    public function verify_pan($id)
    {
        $customer = $this->customers->find($id);
        if (! $customer) {
            show_404();

            return;
        }

        $pan_number = strtoupper(trim((string) $this->input->post('pan_number')));
        if (strlen($pan_number) !== 10 || ! preg_match('/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/', $pan_number)) {
            return $this->_kyc_fail($id, 'PAN number is required and must match the PAN format (5 letters, 4 digits, 1 letter).');
        }

        // Call PAN validation API + fuzzy-match name against customers.name
        // (Levenshtein <= 2) here -- external service, not implemented; the
        // Laravel source itself also stubs this to `true`.
        $name_match = true;

        $verification_id = $this->pan_verifications->insert(array(
            'customer_id' => $customer['id'],
            'pan_number' => $pan_number,
            'is_verified' => 1,
            'name_match' => $name_match ? 1 : 0,
        ));

        $verification = $this->pan_verifications->find($verification_id);

        // pan_number is stored unmasked on this row -- exclude it from the audit log.
        $this->audit_log(
            'Customer',
            $customer['id'],
            'KYC_PAN_VALIDATE',
            null,
            array_diff_key($verification, array_flip(array('pan_number')))
        );

        $this->session->set_flashdata('status', 'PAN verification recorded for ' . htmlspecialchars($customer['name']) . '.');
        redirect('admin/customers/' . $id);
    }

    /**
     * POST /admin/customers/(:num)/nominee -- port of
     * Api\V1\Customer::add_nominee() (application/controllers/api/v1/Customer.php).
     */
    public function add_nominee($id)
    {
        $customer = $this->customers->find($id);
        if (! $customer) {
            show_404();

            return;
        }

        $name = trim((string) $this->input->post('name'));
        $relation = trim((string) $this->input->post('relation'));
        $mobile = trim((string) $this->input->post('mobile'));
        $id_proof_type = trim((string) $this->input->post('id_proof_type'));
        $id_proof_number = trim((string) $this->input->post('id_proof_number'));

        if ($name === '' || strlen($name) > 150) {
            return $this->_kyc_fail($id, 'Nominee name is required and must be at most 150 characters.');
        }
        if ($relation === '' || strlen($relation) > 50) {
            return $this->_kyc_fail($id, 'Nominee relation is required and must be at most 50 characters.');
        }
        if ($mobile !== '' && strlen($mobile) !== 10) {
            return $this->_kyc_fail($id, 'Nominee mobile must be exactly 10 digits.');
        }

        $nominee_id = $this->nominees->insert(array(
            'customer_id' => $id,
            'name' => $name,
            'relation' => $relation,
            'mobile' => $mobile !== '' ? $mobile : null,
            'id_proof_type' => $id_proof_type !== '' ? $id_proof_type : null,
            'id_proof_number' => $id_proof_number !== '' ? $id_proof_number : null,
        ));

        $this->audit_log('Customer', $id, 'NOMINEE_ADD', null, $this->nominees->find($nominee_id));

        $this->session->set_flashdata('status', 'Nominee added for ' . htmlspecialchars($customer['name']) . '.');
        redirect('admin/customers/' . $id);
    }

    /**
     * POST /admin/customers/(:num)/details -- direct edit of email, PAN
     * number and Aadhaar on the customer record itself. Distinct from the
     * Aadhaar/PAN "Verify" actions above, which record a verification event
     * (aadhaar_verifications / pan_verifications) -- this just corrects the
     * stored value. Email and PAN are editable/clearable since the form
     * pre-fills the current value; the Aadhaar number is never pre-filled
     * (only aadhaar_last4 is known), so a blank Aadhaar field always means
     * "leave unchanged" here.
     */
    public function update_details($id)
    {
        $customer = $this->customers->find($id);
        if (! $customer) {
            show_404();

            return;
        }

        $email = trim((string) $this->input->post('email'));
        if ($email !== '' && ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->_kyc_fail($id, 'Please enter a valid email address.');
        }

        $pan_number = strtoupper(trim((string) $this->input->post('pan_number')));
        if ($pan_number !== '' && ! preg_match('/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/', $pan_number)) {
            return $this->_kyc_fail($id, 'PAN number must match the PAN format (5 letters, 4 digits, 1 letter).');
        }

        $aadhaar_number = trim((string) $this->input->post('aadhaar_number'));
        if ($aadhaar_number !== '' && ! preg_match('/^\d{12}$/', $aadhaar_number)) {
            return $this->_kyc_fail($id, 'Aadhaar number must be exactly 12 digits.');
        }

        $update = array(
            'email' => $email !== '' ? $email : null,
            'pan_number' => $pan_number !== '' ? $pan_number : null,
        );
        // Never persist the full Aadhaar number -- same rule as verify_aadhaar().
        if ($aadhaar_number !== '') {
            $update['aadhaar_last4'] = substr($aadhaar_number, -4);
            $update['aadhaar_hash'] = hash('sha256', $aadhaar_number);
        }

        $this->customers->update($id, $update);

        $this->audit_log('Customer', $id, 'CUSTOMER_DETAILS_UPDATE', array(
            'email' => $customer['email'],
            'pan_number' => $customer['pan_number'] ? '(redacted)' : null,
            'aadhaar_last4' => $customer['aadhaar_last4'],
        ), array(
            'email' => $update['email'],
            'pan_number' => $update['pan_number'] ? '(redacted)' : null,
            'aadhaar_last4' => $update['aadhaar_last4'] ?? $customer['aadhaar_last4'],
        ));

        $this->session->set_flashdata('status', 'Customer details updated.');
        redirect('admin/customers/' . $id);
    }

    /** GET /admin/customers/(:num)/photo -- gated inline photo view, same pattern as Kyc::file(). */
    public function photo($id)
    {
        $customer = $this->customers->find($id);
        if (! $customer || empty($customer['photo_path'])) {
            show_404();

            return;
        }

        $path = FCPATH . 'uploads/' . $customer['photo_path'];
        if (! is_file($path)) {
            show_404();

            return;
        }

        $this->audit_log('Customer', $id, 'CUSTOMER_PHOTO_VIEW', null, null);

        $mime = function_exists('mime_content_type') ? mime_content_type($path) : 'application/octet-stream';
        header('Content-Type: ' . $mime);
        header('Content-Disposition: inline; filename="' . basename($path) . '"');
        header('Content-Length: ' . filesize($path));
        readfile($path);
        exit;
    }

    /**
     * POST /admin/customers/(:num)/photo -- upload/replace the customer's
     * photo. Same upload conventions as the cust_photo handling in
     * Loans::store() (uploads/customer-photos, jpg|jpeg|png|webp, 5120kb max).
     */
    public function update_photo($id)
    {
        $customer = $this->customers->find($id);
        if (! $customer) {
            show_404();

            return;
        }

        if (empty($_FILES['photo']) || empty($_FILES['photo']['name'])) {
            return $this->_kyc_fail($id, 'Please choose a photo to upload.');
        }

        if ($_FILES['photo']['size'] > 5120 * 1024) {
            return $this->_kyc_fail($id, 'Customer photo must not be greater than 5120 kilobytes.');
        }

        $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        if (! in_array($ext, array('jpg', 'jpeg', 'png', 'webp'), true)) {
            return $this->_kyc_fail($id, 'Customer photo must be a jpg, jpeg, png, or webp file.');
        }

        $upload_dir = FCPATH . 'uploads/customer-photos';
        if (! is_dir($upload_dir)) {
            mkdir($upload_dir, 0775, true);
        }

        $this->load->library('upload', array(
            'upload_path' => $upload_dir,
            'allowed_types' => 'jpg|jpeg|png|webp',
            'max_size' => 5120,
            'encrypt_name' => true,
        ));

        if (! $this->upload->do_upload('photo')) {
            return $this->_kyc_fail($id, $this->upload->display_errors('', ''));
        }

        $uploaded = $this->upload->data();
        $photo_path = 'customer-photos/' . $uploaded['file_name'];

        $old_photo_path = $customer['photo_path'];

        $this->customers->update($id, array('photo_path' => $photo_path));

        if (! empty($old_photo_path)) {
            $old_full_path = FCPATH . 'uploads/' . $old_photo_path;
            if (is_file($old_full_path)) {
                unlink($old_full_path);
            }
        }

        $this->audit_log('Customer', $id, 'CUSTOMER_PHOTO_UPDATE', array('photo_path' => $old_photo_path), array('photo_path' => $photo_path));

        $this->session->set_flashdata('status', 'Customer photo updated.');
        redirect('admin/customers/' . $id);
    }

    private function _kyc_fail($id, $message)
    {
        $this->session->set_flashdata('error', $message);
        redirect('admin/customers/' . $id);
    }
}
