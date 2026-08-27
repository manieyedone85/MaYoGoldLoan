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

    private function _kyc_fail($id, $message)
    {
        $this->session->set_flashdata('error', $message);
        redirect('admin/customers/' . $id);
    }
}
