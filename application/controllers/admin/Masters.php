<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Admin panel: Masters (Branches, Schemes/Loan Products, Roles, Gold Rates,
 * Vaults, GL Accounts, Approval Limits).
 * Not a Laravel port -- added for BRD §4/§3 "Administration"
 * (docs/BRD_COVERAGE_AUDIT.md). Branches/Loan Products/Roles previously only
 * had API-level Create+Read (or Read-only for Roles); this is the first
 * admin-panel screen for any of them, following the same
 * list-page-plus-modal pattern as admin/Employees.php.
 *
 * Now that admin-panel login is open to every staff role (not just
 * ADMIN/OPERATIONS), this controller's pure-config tabs (Branches, Schemes,
 * Roles, Vaults, GL Accounts, Approval Limits) each gate their own
 * create/update actions to ADMIN/OPERATIONS individually -- but index()
 * itself stays reachable by everyone, and the Rates tab's propose/approve
 * actions are gated to APPRAISER/BRANCH_MANAGER/REGIONAL_MANAGER (mirroring
 * `api/v1/Jewellery.php::propose_rate()/approve_rate()`) since that's a
 * genuine maker-checker workflow, not admin config.
 */
class Masters extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        // No blanket gate here: index() must stay reachable by APPRAISER/
        // BRANCH_MANAGER/REGIONAL_MANAGER too, since gold-rate propose/approve
        // lives on this page. Every config-CRUD action below (branches,
        // schemes, roles, vaults, GL accounts, approval limits) gates itself
        // to ADMIN/OPERATIONS individually instead.

        $this->load->model('Branch_model', 'branches');
        $this->load->model('Loan_product_model', 'loan_products');
        $this->load->model('Gold_rate_model', 'gold_rates');
        $this->load->model('Vault_model', 'vaults');
        $this->load->model('Gl_account_model', 'gl_accounts');
        $this->load->model('Loan_approval_limit_model', 'approval_limits');
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
            'vaults' => $this->vaults->all(array(), 'name ASC'),
            'gl_accounts' => $this->gl_accounts->all(array(), 'code ASC'),
            'approval_limits' => $this->approval_limits->with_relations(),
            // Propose/approve rate are role-gated per-action below (not the whole
            // controller, since APPRAISER/BRANCH_MANAGER/REGIONAL_MANAGER need
            // access to just those two actions, unlike the rest of Masters).
            'can_propose_rate' => in_array($this->user['role_code'], array('APPRAISER', 'BRANCH_MANAGER', 'ADMIN'), true),
            'can_approve_rate' => in_array($this->user['role_code'], array('BRANCH_MANAGER', 'REGIONAL_MANAGER', 'ADMIN'), true),
            'is_ops' => in_array($this->user['role_code'], array('OPERATIONS', 'ADMIN'), true),
        ));
    }

    /**
     * POST /admin/masters/rate/propose -- role APPRAISER/BRANCH_MANAGER.
     * Ports Jewellery::propose_rate() -- gold rates stay a real maker-checker
     * even though they're now reachable from the admin panel, since the
     * panel itself is no longer ADMIN/OPERATIONS-only.
     */
    public function propose_rate()
    {
        if (! $this->require_admin_role(array('APPRAISER', 'BRANCH_MANAGER'))) {
            return;
        }

        $rate_per_gram = $this->input->post('rate_per_gram');
        if (! is_numeric($rate_per_gram) || (float) $rate_per_gram < 0) {
            return $this->_fail('Rate per gram is required and must be a non-negative number.');
        }

        $karat = trim((string) $this->input->post('karat'));
        if ($karat === '' || strlen($karat) > 5) {
            return $this->_fail('Karat is required and must be at most 5 characters.');
        }

        $effective_date = trim((string) $this->input->post('effective_date'));
        if ($effective_date === '' || strtotime($effective_date) === false) {
            return $this->_fail('A valid effective date is required.');
        }

        $ltv_pct = $this->input->post('ltv_pct');
        if ($ltv_pct !== '' && $ltv_pct !== null && (! is_numeric($ltv_pct) || (float) $ltv_pct <= 0 || (float) $ltv_pct > 100)) {
            return $this->_fail('LTV % must be a number between 0 and 100.');
        }

        $this->gold_rates->insert(array(
            'rate_per_gram' => $rate_per_gram,
            'ltv_pct' => ($ltv_pct !== '' && $ltv_pct !== null) ? $ltv_pct : 75.00,
            'karat' => $karat,
            'effective_date' => date('Y-m-d', strtotime($effective_date)),
            'status' => 'PENDING_APPROVAL',
            'proposed_by' => $this->user['id'],
        ));

        $this->session->set_flashdata('status', 'Gold rate proposed, pending approval.');
        redirect('admin/masters');
    }

    /** POST /admin/masters/rate/(:num)/approve -- role BRANCH_MANAGER/REGIONAL_MANAGER */
    public function approve_rate($id)
    {
        if (! $this->require_admin_role(array('BRANCH_MANAGER', 'REGIONAL_MANAGER'))) {
            return;
        }

        $rate = $this->gold_rates->find($id);
        if (! $rate) {
            show_404();

            return;
        }

        $this->gold_rates->update($id, array(
            'status' => 'APPROVED',
            'approved_by' => $this->user['id'],
            'approved_at' => date('Y-m-d H:i:s'),
        ));

        $this->audit_log('GoldRate', $id, 'RATE_APPROVE', array('status' => $rate['status']), array('status' => 'APPROVED'));

        $this->session->set_flashdata('status', 'Gold rate approved.');
        redirect('admin/masters');
    }

    /** POST /admin/masters/vault/create */
    public function store_vault()
    {
        if (! $this->require_admin_role(array('OPERATIONS'))) {
            return;
        }

        $branch_id = $this->input->post('branch_id');
        $name = trim((string) $this->input->post('name'));
        if (! $branch_id || ! $this->branches->find($branch_id) || $name === '') {
            return $this->_fail('A valid branch and name are required.');
        }

        $this->vaults->insert(array('branch_id' => $branch_id, 'name' => $name));

        $this->session->set_flashdata('status', 'Vault created.');
        redirect('admin/masters');
    }

    /** POST /admin/masters/vault/(:num) */
    public function update_vault($id)
    {
        if (! $this->require_admin_role(array('OPERATIONS'))) {
            return;
        }

        if (! $this->vaults->find($id)) {
            show_404();

            return;
        }

        $branch_id = $this->input->post('branch_id');
        $name = trim((string) $this->input->post('name'));
        if (! $branch_id || ! $this->branches->find($branch_id) || $name === '') {
            return $this->_fail('A valid branch and name are required.');
        }

        $this->vaults->update($id, array('branch_id' => $branch_id, 'name' => $name));

        $this->session->set_flashdata('status', 'Vault updated.');
        redirect('admin/masters');
    }

    /** POST /admin/masters/gl-account/create */
    public function store_gl_account()
    {
        if (! $this->require_admin_role(array('OPERATIONS'))) {
            return;
        }

        $data = $this->_collect(array('code', 'name', 'type'));

        if ($data['code'] === '' || $data['name'] === '' || ! in_array($data['type'], array('ASSET', 'LIABILITY', 'INCOME', 'EXPENSE'), true)) {
            return $this->_fail('Code, name, and a valid type (Asset/Liability/Income/Expense) are required.');
        }
        if ($this->gl_accounts->first(array('code' => $data['code']))) {
            return $this->_fail('Code is already in use.');
        }

        $this->gl_accounts->insert($data);

        $this->session->set_flashdata('status', 'GL account created.');
        redirect('admin/masters');
    }

    /** POST /admin/masters/gl-account/(:num) */
    public function update_gl_account($id)
    {
        if (! $this->require_admin_role(array('OPERATIONS'))) {
            return;
        }

        if (! $this->gl_accounts->find($id)) {
            show_404();

            return;
        }

        $data = $this->_collect(array('code', 'name', 'type'));

        if ($data['code'] === '' || $data['name'] === '' || ! in_array($data['type'], array('ASSET', 'LIABILITY', 'INCOME', 'EXPENSE'), true)) {
            return $this->_fail('Code, name, and a valid type (Asset/Liability/Income/Expense) are required.');
        }
        if ($this->gl_accounts->first(array('code' => $data['code'], 'id !=' => $id))) {
            return $this->_fail('Code is already in use.');
        }

        $this->gl_accounts->update($id, $data);

        $this->session->set_flashdata('status', 'GL account updated.');
        redirect('admin/masters');
    }

    /** POST /admin/masters/approval-limit/create */
    public function store_approval_limit()
    {
        if (! $this->require_admin_role(array('OPERATIONS'))) {
            return;
        }

        $role_id = $this->input->post('role_id');
        $max_amount = $this->input->post('max_amount');
        if (! $role_id || ! $this->roles->find($role_id) || ! is_numeric($max_amount) || (float) $max_amount < 0) {
            return $this->_fail('A valid role and a non-negative max amount are required.');
        }

        $this->approval_limits->insert(array('role_id' => $role_id, 'max_amount' => $max_amount));

        $this->session->set_flashdata('status', 'Approval limit created.');
        redirect('admin/masters');
    }

    /** POST /admin/masters/approval-limit/(:num) */
    public function update_approval_limit($id)
    {
        if (! $this->require_admin_role(array('OPERATIONS'))) {
            return;
        }

        if (! $this->approval_limits->find($id)) {
            show_404();

            return;
        }

        $max_amount = $this->input->post('max_amount');
        if (! is_numeric($max_amount) || (float) $max_amount < 0) {
            return $this->_fail('Max amount must be a non-negative number.');
        }

        $this->approval_limits->update($id, array('max_amount' => $max_amount));

        $this->session->set_flashdata('status', 'Approval limit updated.');
        redirect('admin/masters');
    }

    /** POST /admin/masters/branch/create */
    public function store_branch()
    {
        if (! $this->require_admin_role(array('OPERATIONS'))) {
            return;
        }

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
        if (! $this->require_admin_role(array('OPERATIONS'))) {
            return;
        }

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
        if (! $this->require_admin_role(array('OPERATIONS'))) {
            return;
        }

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
        if (! $this->require_admin_role(array('OPERATIONS'))) {
            return;
        }

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
        if (! $this->require_admin_role(array('OPERATIONS'))) {
            return;
        }

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
        if (! $this->require_admin_role(array('OPERATIONS'))) {
            return;
        }

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
