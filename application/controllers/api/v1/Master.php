<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Mirrors app/Http/Controllers/Api/V1/MasterController.php.
 * All methods require auth + role:ADMIN,OPERATIONS (see routes_modules/api_master.php).
 */
class Master extends Api_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->require_auth();
        $this->require_role(array('ADMIN', 'OPERATIONS'));
        $this->load->model('Branch_model', 'branches');
        $this->load->model('Loan_product_model', 'loan_products');
        $this->load->model('Role_model', 'roles');
        $this->load->model('Gold_rate_model', 'gold_rates');
    }

    /** GET /api/v1/master/branch */
    public function branch_index()
    {
        return json_response(array('data' => $this->branches->all()));
    }

    /** POST /api/v1/master/branch */
    public function branch_store()
    {
        $data = $this->json_input();

        if (empty($data['branch_code']) || empty($data['name'])) {
            return json_error('branch_code and name are required.');
        }

        if ($this->branches->first(array('branch_code' => $data['branch_code']))) {
            return json_error('branch_code already exists.');
        }

        $id = $this->branches->insert(array(
            'branch_code' => $data['branch_code'],
            'name' => $data['name'],
            'city' => $data['city'] ?? null,
            'state' => $data['state'] ?? null,
        ));

        $branch = $this->branches->find($id);
        $this->audit_log('Branch', $id, 'CREATE', null, $branch);

        return json_response(array('data' => $branch), 201);
    }

    /**
     * PUT /api/v1/master/branch/{id}
     * Not a Laravel port -- added for BRD §4/§3 "Administration": this row
     * (docs/BRD_COVERAGE_AUDIT.md) previously claimed "full CRUD" existed,
     * but only Create+Read did -- there was no way to edit a branch once
     * created.
     */
    public function branch_update($id)
    {
        $branch = $this->branches->find($id);
        if (! $branch) {
            return json_error('Branch not found.', 404);
        }

        $data = $this->json_input();

        if (empty($data['name'])) {
            return json_error('name is required.');
        }
        if (! empty($data['branch_code']) && $data['branch_code'] !== $branch['branch_code']
            && $this->branches->first(array('branch_code' => $data['branch_code']))) {
            return json_error('branch_code already exists.');
        }

        $update = array(
            'branch_code' => $data['branch_code'] ?? $branch['branch_code'],
            'name' => $data['name'],
            'city' => $data['city'] ?? $branch['city'],
            'state' => $data['state'] ?? $branch['state'],
            'gst_number' => $data['gst_number'] ?? $branch['gst_number'],
            'is_active' => isset($data['is_active']) ? ($data['is_active'] ? 1 : 0) : $branch['is_active'],
        );

        $this->branches->update($id, $update);

        $updated = $this->branches->find($id);
        $this->audit_log('Branch', $id, 'UPDATE', $branch, $updated);

        return json_response(array('data' => $updated));
    }

    /** GET /api/v1/master/loan-product */
    public function loan_product_index()
    {
        return json_response(array('data' => $this->loan_products->all()));
    }

    /** POST /api/v1/master/loan-product */
    public function loan_product_store()
    {
        $data = $this->json_input();
        $required = array('code', 'name', 'interest_rate_pct', 'interest_type', 'tenure_months');

        foreach ($required as $field) {
            if (! isset($data[$field]) || $data[$field] === '') {
                return json_error("{$field} is required.");
            }
        }

        if ($this->loan_products->first(array('code' => $data['code']))) {
            return json_error('code already exists.');
        }

        $id = $this->loan_products->insert(array(
            'code' => $data['code'],
            'name' => $data['name'],
            'interest_rate_pct' => $data['interest_rate_pct'],
            'interest_type' => $data['interest_type'],
            'tenure_months' => $data['tenure_months'],
            'processing_fee_pct' => $data['processing_fee_pct'] ?? 0,
            'gst_pct' => $data['gst_pct'] ?? 18.00,
            'insurance_pct' => $data['insurance_pct'] ?? 0,
        ));

        $loan_product = $this->loan_products->find($id);
        $this->audit_log('LoanProduct', $id, 'CREATE', null, $loan_product);

        return json_response(array('data' => $loan_product), 201);
    }

    /**
     * PUT /api/v1/master/loan-product/{id}
     * Not a Laravel port -- added for BRD §4/§3 "Administration": same "full
     * CRUD" claim as branches turned out to be Create+Read only, and this is
     * also where "Charges" (§4/§3's `processing_fee_pct`/`gst_pct`/
     * `insurance_pct`) actually lives -- there's no separate charges-master
     * table, so making these editable here closes that row too.
     */
    public function loan_product_update($id)
    {
        $loan_product = $this->loan_products->find($id);
        if (! $loan_product) {
            return json_error('Loan product not found.', 404);
        }

        $data = $this->json_input();
        $required = array('name', 'interest_rate_pct', 'interest_type', 'tenure_months');
        foreach ($required as $field) {
            if (! isset($data[$field]) || $data[$field] === '') {
                return json_error("{$field} is required.");
            }
        }

        if (! empty($data['code']) && $data['code'] !== $loan_product['code']
            && $this->loan_products->first(array('code' => $data['code']))) {
            return json_error('code already exists.');
        }

        $update = array(
            'code' => $data['code'] ?? $loan_product['code'],
            'name' => $data['name'],
            'interest_rate_pct' => $data['interest_rate_pct'],
            'interest_type' => $data['interest_type'],
            'tenure_months' => $data['tenure_months'],
            'processing_fee_pct' => $data['processing_fee_pct'] ?? $loan_product['processing_fee_pct'],
            'gst_pct' => $data['gst_pct'] ?? $loan_product['gst_pct'],
            'insurance_pct' => $data['insurance_pct'] ?? $loan_product['insurance_pct'],
            'is_active' => isset($data['is_active']) ? ($data['is_active'] ? 1 : 0) : $loan_product['is_active'],
        );

        $this->loan_products->update($id, $update);

        $updated = $this->loan_products->find($id);
        $this->audit_log('LoanProduct', $id, 'UPDATE', $loan_product, $updated);

        return json_response(array('data' => $updated));
    }

    /** GET /api/v1/master/role */
    public function role_index()
    {
        return json_response(array('data' => $this->roles->all()));
    }

    /**
     * POST /api/v1/master/role
     * Not a Laravel port -- added for BRD §4/§3 "Administration": `role_index()`
     * was read-only, with no way to create or edit a role.
     */
    public function role_store()
    {
        $data = $this->json_input();

        if (empty($data['code']) || empty($data['name'])) {
            return json_error('code and name are required.');
        }
        if ($this->roles->find_by_code($data['code'])) {
            return json_error('code already exists.');
        }

        $id = $this->roles->insert(array(
            'code' => strtoupper($data['code']),
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
        ));

        $role = $this->roles->find($id);
        $this->audit_log('Role', $id, 'CREATE', null, $role);

        return json_response(array('data' => $role), 201);
    }

    /**
     * PUT /api/v1/master/role/{id}
     * `code` is intentionally not editable here -- it's matched against
     * elsewhere in the app (e.g. require_role()'s role-code arrays), so
     * renaming it out from under existing checks would silently break them.
     */
    public function role_update($id)
    {
        $role = $this->roles->find($id);
        if (! $role) {
            return json_error('Role not found.', 404);
        }

        $data = $this->json_input();
        if (empty($data['name'])) {
            return json_error('name is required.');
        }

        $update = array(
            'name' => $data['name'],
            'description' => $data['description'] ?? $role['description'],
        );

        $this->roles->update($id, $update);

        $updated = $this->roles->find($id);
        $this->audit_log('Role', $id, 'UPDATE', $role, $updated);

        return json_response(array('data' => $updated));
    }

    /**
     * GET /api/v1/master/gold-rate
     * Not a Laravel port -- added for BRD §4/§3 "Rates" ("no standalone
     * interest-rate management screen"). Read-only here deliberately:
     * propose/approve stay on Jewellery.php (APPRAISER/BRANCH_MANAGER/
     * REGIONAL_MANAGER roles), since Master.php's own constructor requires
     * ADMIN/OPERATIONS -- this just gives that audience visibility into the
     * rate history/approval trail, not a duplicate action path.
     */
    public function gold_rate_index()
    {
        return json_response(array('data' => $this->gold_rates->all(array(), 'effective_date DESC')));
    }
}
