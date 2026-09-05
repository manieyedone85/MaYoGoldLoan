<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Mirrors app/Http/Controllers/Api/V1/CustomerController.php.
 * Every route in this module sits behind the Laravel route group
 * `Route::middleware(['auth:sanctum', 'device.binding'])` (see
 * routes/api.php "Module 2: Customer Management"), so every method here
 * calls require_auth() + require_device_binding(). store() additionally
 * replicates StoreCustomerRequest::authorize() (BRANCH_EXECUTIVE,
 * BRANCH_MANAGER, ADMIN only) and merge() replicates the route's explicit
 * `role:REGIONAL_MANAGER,ADMIN` middleware.
 */
class Customer extends Api_Controller
{
    /** Allowed customers.profession_type values -- plain VARCHAR, app-enforced (see docs/migrations/2026_08_26_customer_profile_fields.sql). Mirrors admin\Loans::PROFESSION_TYPES. */
    const PROFESSION_TYPES = array('SALARIED', 'SELF_EMPLOYED', 'BUSINESS', 'AGRICULTURE', 'RETIRED', 'OTHER');

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Customer_model', 'customers');
        $this->load->model('Customer_address_model', 'addresses');
        $this->load->model('Customer_family_member_model', 'family_members');
        $this->load->model('Customer_nominee_model', 'nominees');
        $this->load->model('Customer_merge_log_model', 'merge_logs');
        $this->load->model('Customer_duplicate_log_model', 'duplicate_logs');
        $this->load->model('Branch_model', 'branches');
    }

    /** POST /api/v1/customer */
    public function store()
    {
        $user = $this->require_auth();
        $this->require_device_binding();
        $this->require_role(array('BRANCH_EXECUTIVE', 'BRANCH_MANAGER', 'ADMIN'));

        $data = $this->json_input();

        if (empty($data['name']) || strlen((string) $data['name']) > 150) {
            return json_error('name is required and must be at most 150 characters.');
        }
        if (empty($data['mobile']) || strlen((string) $data['mobile']) !== 10) {
            return json_error('mobile is required and must be exactly 10 characters.');
        }
        if (! empty($data['email']) && (strlen((string) $data['email']) > 150 || ! filter_var($data['email'], FILTER_VALIDATE_EMAIL))) {
            return json_error('email must be a valid email address of at most 150 characters.');
        }
        if (! empty($data['dob']) && strtotime($data['dob']) === false) {
            return json_error('dob must be a valid date.');
        }
        if (! empty($data['gender']) && ! in_array($data['gender'], array('MALE', 'FEMALE', 'OTHER'), true)) {
            return json_error('gender must be one of MALE, FEMALE, OTHER.');
        }
        if (! empty($data['profession_type']) && ! in_array($data['profession_type'], self::PROFESSION_TYPES, true)) {
            return json_error('profession_type must be one of ' . implode(', ', self::PROFESSION_TYPES) . '.');
        }
        if (isset($data['income']) && $data['income'] !== '' && (! is_numeric($data['income']) || (float) $data['income'] < 0)) {
            return json_error('income must be a non-negative number.');
        }
        if (empty($data['branch_id']) || ! $this->branches->find($data['branch_id'])) {
            return json_error('branch_id is required and must reference an existing branch.');
        }
        if (empty($data['address']) || ! is_array($data['address'])) {
            return json_error('address is required.');
        }
        $address = $data['address'];
        if (empty($address['line1']) || strlen((string) $address['line1']) > 255) {
            return json_error('address.line1 is required and must be at most 255 characters.');
        }
        if (empty($address['city']) || strlen((string) $address['city']) > 100) {
            return json_error('address.city is required and must be at most 100 characters.');
        }
        if (empty($address['state']) || strlen((string) $address['state']) > 100) {
            return json_error('address.state is required and must be at most 100 characters.');
        }
        if (empty($address['pincode']) || strlen((string) $address['pincode']) > 10) {
            return json_error('address.pincode is required and must be at most 10 characters.');
        }

        // File I/O happens outside the DB transaction below, and before it --
        // a failed upload must not leave a half-started transaction. Same
        // uploads/customer-photos directory as admin/Loans::store().
        $photo_path = null;
        if (! empty($_FILES['photo']['name'])) {
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
                return json_error($this->upload->display_errors('', ''));
            }

            $uploaded_photo = $this->upload->data();
            $photo_path = 'customer-photos/' . $uploaded_photo['file_name'];
        }

        $this->db->trans_start();

        $customer_id = $this->customers->insert(array(
            'customer_code' => $this->customers->next_customer_code(),
            'name' => $data['name'],
            'mobile' => $data['mobile'],
            'email' => $data['email'] ?? null,
            'dob' => $data['dob'] ?? null,
            'gender' => $data['gender'] ?? null,
            'father_name' => $data['father_name'] ?? null,
            'profession_type' => $data['profession_type'] ?? null,
            'profession_details' => $data['profession_details'] ?? null,
            'income' => (isset($data['income']) && $data['income'] !== '') ? $data['income'] : null,
            'photo_path' => $photo_path,
            'branch_id' => $data['branch_id'],
            'registered_by' => $user['id'],
            'kyc_status' => 'PENDING',
        ));

        $this->addresses->insert(array(
            'customer_id' => $customer_id,
            'type' => 'CURRENT',
            'line1' => $address['line1'],
            'line2' => $address['line2'] ?? null,
            'city' => $address['city'],
            'state' => $address['state'],
            'pincode' => $address['pincode'],
        ));

        $duplicates = $this->detect_duplicates($customer_id, $data['mobile'], $data['aadhaar_hash'] ?? null);

        $this->db->trans_complete();

        if (! $this->db->trans_status()) {
            return json_error('Failed to create customer.', 500);
        }

        $customer = $this->customers->find($customer_id);
        $customer['addresses'] = $this->addresses->for_customer($customer_id);
        $customer['possible_duplicates'] = $duplicates;

        $this->audit_log('Customer', $customer_id, 'CREATE', null, $customer);

        return json_response(array('data' => $customer), 201);
    }

    /** GET /api/v1/customer/{id} */
    public function show($id)
    {
        $this->require_auth();
        $this->require_device_binding();

        $customer = $this->customers->find($id);
        if (! $customer) {
            return json_error('Customer not found.', 404);
        }

        $customer['addresses'] = $this->addresses->for_customer($id);
        $customer['family_members'] = $this->family_members->for_customer($id);
        $customer['nominees'] = $this->nominees->for_customer($id);

        return json_response(array('data' => $customer));
    }

    /** GET /api/v1/customer/search?mobile=&aadhaar_hash= */
    public function search()
    {
        $this->require_auth();
        $this->require_device_binding();

        $mobile = $this->input->get('mobile');
        $aadhaar_hash = $this->input->get('aadhaar_hash');

        $where = array();
        // Laravel builds mobile AND aadhaar_hash as independent optional filters
        // (both applied with `where` when present); replicate that here.
        if ($mobile !== null && $mobile !== '') {
            $where['mobile'] = $mobile;
        }
        if ($aadhaar_hash !== null && $aadhaar_hash !== '') {
            $where['aadhaar_hash'] = $aadhaar_hash;
        }

        $query = $this->db->from('customers')->where('deleted_at IS NULL', null, false);
        if (! empty($where)) {
            $query->where($where);
        }
        $results = $query->limit(20)->get()->result_array();

        return json_response(array('data' => $results));
    }

    /**
     * Persists a `customer_duplicate_log` row (status PENDING_REVIEW, ready
     * for a reviewer to CONFIRM/DISMISS) for every existing, non-deleted
     * customer that shares this new customer's mobile or aadhaar_hash.
     * Called from store() so duplicate creation is actually detected and
     * logged, not just reported back on request via duplicate_check() —
     * see BRD §7 "duplicate customer creation is detected" in
     * docs/BRD_COVERAGE_AUDIT.md. Never blocks the insert: a shared mobile
     * number can be legitimate (family members), so this flags for human
     * review rather than rejecting the request.
     */
    private function detect_duplicates($customer_id, $mobile, $aadhaar_hash)
    {
        // Select only what's needed to score the match (aadhaar_hash) plus
        // enough for a reviewer to recognize the candidate -- never the full
        // row. This used to be an unqualified `SELECT *`, so aadhaar_hash,
        // aadhaar_last4, pan_number, dob and email for a customer at ANY
        // branch were returned straight back to the (possibly unrelated)
        // caller in the 201 response and copied into audit_log's JSON,
        // turning customer creation into a PII enumeration oracle over the
        // whole customer base.
        $query = $this->db->select('id, customer_code, name, mobile, branch_id, kyc_status, aadhaar_hash')
            ->from('customers')
            ->where('deleted_at IS NULL', null, false)
            ->where('id !=', $customer_id)
            ->group_start()
                ->where('mobile', $mobile);

        if (! empty($aadhaar_hash)) {
            $query->or_where('aadhaar_hash', $aadhaar_hash);
        }

        $matches = $query->group_end()->get()->result_array();

        $sanitized = array();
        foreach ($matches as $match) {
            $this->duplicate_logs->insert(array(
                'customer_id' => $customer_id,
                'matched_customer_id' => $match['id'],
                'match_score' => (! empty($aadhaar_hash) && $match['aadhaar_hash'] === $aadhaar_hash) ? 100.00 : 60.00,
            ));

            unset($match['aadhaar_hash']);
            $sanitized[] = $match;
        }

        return $sanitized;
    }

    /** POST /api/v1/customer/duplicate-check */
    public function duplicate_check()
    {
        $this->require_auth();
        $this->require_device_binding();

        $data = $this->json_input();

        if (empty($data['name']) || empty($data['mobile'])) {
            return json_error('name and mobile are required.');
        }

        $query = $this->db->from('customers')
            ->where('deleted_at IS NULL', null, false)
            ->group_start()
                ->where('mobile', $data['mobile']);

        if (! empty($data['aadhaar_hash'])) {
            $query->or_where('aadhaar_hash', $data['aadhaar_hash']);
        }

        $candidates = $query->group_end()->get()->result_array();

        return json_response(array('possible_duplicates' => $candidates));
    }

    /** POST /api/v1/customer/{id}/merge */
    public function merge($id)
    {
        $user = $this->require_auth();
        $this->require_device_binding();
        $this->require_role(array('REGIONAL_MANAGER', 'ADMIN'));

        $data = $this->json_input();

        if (empty($data['merged_customer_id']) || ! $this->customers->find($data['merged_customer_id'])) {
            return json_error('merged_customer_id is required and must reference an existing customer.');
        }

        $customer = $this->customers->find($id);
        if (! $customer) {
            return json_error('Customer not found.', 404);
        }

        $this->merge_logs->insert(array(
            'primary_customer_id' => $id,
            'merged_customer_id' => $data['merged_customer_id'],
            'approved_by' => $user['id'],
        ));

        // Soft-link only — never hard delete the merged record.
        $this->customers->update($data['merged_customer_id'], array('deleted_at' => date('Y-m-d H:i:s')));

        $this->audit_log(
            'Customer',
            $id,
            'MERGE',
            array('merged_customer_id' => null),
            array('merged_customer_id' => $data['merged_customer_id'])
        );

        return json_response(array('message' => 'Customers merged.'));
    }

    /**
     * PUT /api/v1/customer/{id}/kyc-status
     *
     * Not a Laravel port — added for BRD §7 "KYC status: Pending / Verified /
     * Rejected / Expired" (docs/BRD_COVERAGE_AUDIT.md). `customers.kyc_status`
     * was set to PENDING at creation and then never transitioned anywhere in
     * the codebase, and EXPIRED didn't exist as a reachable state at all.
     * Restricted to the same approval-tier roles as loan/merge decisions,
     * since KYC status drives loan eligibility.
     */
    public function update_kyc_status($id)
    {
        $this->require_auth();
        $this->require_device_binding();
        $this->require_role(array('BRANCH_MANAGER', 'REGIONAL_MANAGER', 'ADMIN'));

        $customer = $this->customers->find($id);
        if (! $customer) {
            return json_error('Customer not found.', 404);
        }

        $data = $this->json_input();

        if (empty($data['status']) || ! in_array($data['status'], array('VERIFIED', 'REJECTED', 'EXPIRED'), true)) {
            return json_error('status is required and must be one of VERIFIED, REJECTED, EXPIRED.');
        }
        if (in_array($data['status'], array('REJECTED', 'EXPIRED'), true) && empty($data['reason'])) {
            return json_error('reason is required when setting status to REJECTED or EXPIRED.');
        }

        $before = array('kyc_status' => $customer['kyc_status']);

        $this->customers->update($id, array('kyc_status' => $data['status']));

        $after = array('kyc_status' => $data['status'], 'reason' => $data['reason'] ?? null);
        $this->audit_log('Customer', $id, 'KYC_STATUS_UPDATE', $before, $after);

        return json_response(array('data' => $this->customers->find($id)));
    }

    /** POST /api/v1/customer/{id}/nominee */
    public function add_nominee($id)
    {
        $this->require_auth();
        $this->require_device_binding();

        $customer = $this->customers->find($id);
        if (! $customer) {
            return json_error('Customer not found.', 404);
        }

        $data = $this->json_input();

        if (empty($data['name']) || strlen((string) $data['name']) > 150) {
            return json_error('name is required and must be at most 150 characters.');
        }
        if (empty($data['relation']) || strlen((string) $data['relation']) > 50) {
            return json_error('relation is required and must be at most 50 characters.');
        }
        if (! empty($data['mobile']) && strlen((string) $data['mobile']) !== 10) {
            return json_error('mobile must be exactly 10 characters.');
        }

        $nominee_id = $this->nominees->insert(array(
            'customer_id' => $id,
            'name' => $data['name'],
            'relation' => $data['relation'],
            'mobile' => $data['mobile'] ?? null,
            'id_proof_type' => $data['id_proof_type'] ?? null,
            'id_proof_number' => $data['id_proof_number'] ?? null,
        ));

        $nominee = $this->nominees->find($nominee_id);

        $this->audit_log('Customer', $id, 'NOMINEE_ADD', null, $nominee);

        return json_response(array('data' => $nominee), 201);
    }

    /**
     * GET /api/v1/customer/{id}/loans
     * All active and closed loans for a customer -- step 3 of the mandatory
     * mobile-number-search scenario (BRD section 10). Mirrors what
     * admin/Customers::show() already does for the web panel, exposed here
     * so the mobile app can do the same search-by-mobile -> loan list flow.
     */
    public function loans($id)
    {
        $this->require_auth();
        $this->require_device_binding();

        $customer = $this->customers->find($id);
        if (! $customer) {
            return json_error('Customer not found.', 404);
        }

        $this->load->model('Loan_model', 'loans');

        return json_response(array('data' => $this->loans->with_relations(array('loans.customer_id' => $id))));
    }

    /** POST /api/v1/customer/{id}/family-member */
    public function add_family_member($id)
    {
        $this->require_auth();
        $this->require_device_binding();

        $customer = $this->customers->find($id);
        if (! $customer) {
            return json_error('Customer not found.', 404);
        }

        $data = $this->json_input();

        if (empty($data['name']) || strlen((string) $data['name']) > 150) {
            return json_error('name is required and must be at most 150 characters.');
        }
        if (empty($data['relation']) || strlen((string) $data['relation']) > 50) {
            return json_error('relation is required and must be at most 50 characters.');
        }
        if (! empty($data['mobile']) && strlen((string) $data['mobile']) !== 10) {
            return json_error('mobile must be exactly 10 characters.');
        }

        $member_id = $this->family_members->insert(array(
            'customer_id' => $id,
            'name' => $data['name'],
            'relation' => $data['relation'],
            'mobile' => $data['mobile'] ?? null,
        ));

        $family_member = $this->family_members->find($member_id);

        $this->audit_log('Customer', $id, 'FAMILY_MEMBER_ADD', null, $family_member);

        return json_response(array('data' => $family_member), 201);
    }
}
