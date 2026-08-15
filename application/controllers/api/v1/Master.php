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

    /** GET /api/v1/master/role */
    public function role_index()
    {
        return json_response(array('data' => $this->roles->all()));
    }
}
