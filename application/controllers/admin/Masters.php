<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Admin panel: Masters (Branches, Schemes/Loan Products, Roles, Gold Rates).
 * Not a Laravel port -- added for BRD §4/§3 "Administration"
 * (docs/BRD_COVERAGE_AUDIT.md). Branches/Loan Products/Roles previously only
 * had API-level Create+Read (or Read-only for Roles); this is the first
 * admin-panel screen for any of them, following the same
 * list-page-plus-modal pattern as admin/Employees.php.
 *
 * Gold rates are shown read-only here: propose/approve stay on
 * `api/v1/Jewellery.php` (role APPRAISER/BRANCH_MANAGER/REGIONAL_MANAGER),
 * since this controller (like every admin/* controller) only allows
 * ADMIN/OPERATIONS to log in at all -- this page gives that audience
 * visibility into the rate history, not a duplicate action path performed by
 * the wrong roles.
 */
class Masters extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Branch_model', 'branches');
        $this->load->model('Loan_product_model', 'loan_products');
        $this->load->model('Gold_rate_model', 'gold_rates');
        // Admin_Controller already loads Role_model as $this->roles.
    }

    /** GET /admin/masters */
    public function index()
    {
        $this->render('masters', array(
            'page_title' => 'Masters',
            'branches' => $this->branches->all(array(), 'name ASC'),
            'loan_products' => $this->loan_products->all(array(), 'name ASC'),
            'roles' => $this->roles->all(array(), 'name ASC'),
            'gold_rates' => $this->gold_rates->all(array(), 'effective_date DESC'),
        ));
    }

    /** POST /admin/masters/branch/create */
    public function store_branch()
    {
        $data = $this->_collect(array('branch_code', 'name', 'city', 'state', 'gst_number'));

        if ($data['branch_code'] === '' || $data['name'] === '') {
            return $this->_fail('Branch code and name are required.');
        }
        if ($this->branches->first(array('branch_code' => $data['branch_code']))) {
            return $this->_fail('Branch code is already in use.');
        }

        $this->branches->insert(array(
            'branch_code' => $data['branch_code'],
            'name' => $data['name'],
            'city' => $data['city'] !== '' ? $data['city'] : null,
            'state' => $data['state'] !== '' ? $data['state'] : null,
            'gst_number' => $data['gst_number'] !== '' ? $data['gst_number'] : null,
            'is_active' => 1,
        ));

        $this->session->set_flashdata('status', 'Branch created.');
        redirect('admin/masters');
    }

    /** POST /admin/masters/branch/(:num) */
    public function update_branch($id)
    {
        $branch = $this->branches->find($id);
        if (! $branch) {
            show_404();

            return;
        }

        $data = $this->_collect(array('branch_code', 'name', 'city', 'state', 'gst_number'));
        $is_active = $this->input->post('is_active') ? 1 : 0;

        if ($data['branch_code'] === '' || $data['name'] === '') {
            return $this->_fail('Branch code and name are required.');
        }
        if ($this->branches->first(array('branch_code' => $data['branch_code'], 'id !=' => $id))) {
            return $this->_fail('Branch code is already in use.');
        }

        $this->branches->update($id, array(
            'branch_code' => $data['branch_code'],
            'name' => $data['name'],
            'city' => $data['city'] !== '' ? $data['city'] : null,
            'state' => $data['state'] !== '' ? $data['state'] : null,
            'gst_number' => $data['gst_number'] !== '' ? $data['gst_number'] : null,
            'is_active' => $is_active,
        ));

        $this->session->set_flashdata('status', 'Branch updated.');
        redirect('admin/masters');
    }

    /** POST /admin/masters/loan-product/create */
    public function store_loan_product()
    {
        $data = $this->_collect(array('code', 'name', 'interest_rate_pct', 'interest_type', 'tenure_months', 'processing_fee_pct', 'gst_pct', 'insurance_pct'));

        if ($data['code'] === '' || $data['name'] === '' || $data['interest_rate_pct'] === '' || $data['tenure_months'] === '') {
            return $this->_fail('Code, name, interest rate and tenure are required.');
        }
        if ($this->loan_products->first(array('code' => $data['code']))) {
            return $this->_fail('Code is already in use.');
        }

        $this->loan_products->insert(array(
            'code' => $data['code'],
            'name' => $data['name'],
            'interest_rate_pct' => $data['interest_rate_pct'],
            'interest_type' => $data['interest_type'] !== '' ? $data['interest_type'] : 'FLAT',
            'tenure_months' => $data['tenure_months'],
            'processing_fee_pct' => $data['processing_fee_pct'] !== '' ? $data['processing_fee_pct'] : 0,
            'gst_pct' => $data['gst_pct'] !== '' ? $data['gst_pct'] : 18.00,
            'insurance_pct' => $data['insurance_pct'] !== '' ? $data['insurance_pct'] : 0,
            'is_active' => 1,
        ));

        $this->session->set_flashdata('status', 'Scheme created.');
        redirect('admin/masters');
    }

    /** POST /admin/masters/loan-product/(:num) */
    public function update_loan_product($id)
    {
        $loan_product = $this->loan_products->find($id);
        if (! $loan_product) {
            show_404();

            return;
        }

        $data = $this->_collect(array('code', 'name', 'interest_rate_pct', 'interest_type', 'tenure_months', 'processing_fee_pct', 'gst_pct', 'insurance_pct'));
        $is_active = $this->input->post('is_active') ? 1 : 0;

        if ($data['code'] === '' || $data['name'] === '' || $data['interest_rate_pct'] === '' || $data['tenure_months'] === '') {
            return $this->_fail('Code, name, interest rate and tenure are required.');
        }
        if ($this->loan_products->first(array('code' => $data['code'], 'id !=' => $id))) {
            return $this->_fail('Code is already in use.');
        }

        $this->loan_products->update($id, array(
            'code' => $data['code'],
            'name' => $data['name'],
            'interest_rate_pct' => $data['interest_rate_pct'],
            'interest_type' => $data['interest_type'] !== '' ? $data['interest_type'] : 'FLAT',
            'tenure_months' => $data['tenure_months'],
            'processing_fee_pct' => $data['processing_fee_pct'],
            'gst_pct' => $data['gst_pct'],
            'insurance_pct' => $data['insurance_pct'],
            'is_active' => $is_active,
        ));

        $this->session->set_flashdata('status', 'Scheme updated.');
        redirect('admin/masters');
    }

    /** POST /admin/masters/role/create */
    public function store_role()
    {
        $data = $this->_collect(array('code', 'name', 'description'));

        if ($data['code'] === '' || $data['name'] === '') {
            return $this->_fail('Code and name are required.');
        }
        if ($this->roles->find_by_code(strtoupper($data['code']))) {
            return $this->_fail('Code is already in use.');
        }

        $this->roles->insert(array(
            'code' => strtoupper($data['code']),
            'name' => $data['name'],
            'description' => $data['description'] !== '' ? $data['description'] : null,
        ));

        $this->session->set_flashdata('status', 'Role created.');
        redirect('admin/masters');
    }

    /** POST /admin/masters/role/(:num) -- code is intentionally not editable, see Master::role_update(). */
    public function update_role($id)
    {
        $role = $this->roles->find($id);
        if (! $role) {
            show_404();

            return;
        }

        $data = $this->_collect(array('name', 'description'));

        if ($data['name'] === '') {
            return $this->_fail('Name is required.');
        }

        $this->roles->update($id, array(
            'name' => $data['name'],
            'description' => $data['description'] !== '' ? $data['description'] : null,
        ));

        $this->session->set_flashdata('status', 'Role updated.');
        redirect('admin/masters');
    }

    private function _collect(array $fields)
    {
        $data = array();
        foreach ($fields as $field) {
            $data[$field] = trim((string) $this->input->post($field));
        }

        return $data;
    }

    private function _fail($message)
    {
        $this->session->set_flashdata('error', $message);
        redirect('admin/masters');
    }
}
