<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Admin panel: Employees (users whose role isn't CUSTOMER).
 * Port of App\Http\Livewire\Admin\Employees\Index.
 *
 * Livewire's modal + reactive form become a plain list page with a
 * "New Employee" modal and one per-row "Edit" modal, each posting to
 * store()/update($id). See routes.php: admin/employees (index),
 * admin/employees/create (store), admin/employees/(:num) (update),
 * admin/employees/(:num)/toggle (toggle_active).
 */
class Employees extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        // Admin_Controller already loads User_model as $this->users and
        // Role_model as $this->roles.
        $this->load->model('Branch_model', 'branches');

        // Staff/user management stays back-office only, now that login is
        // open to every role -- a field role must not be able to create or
        // reactivate other logins.
        $this->require_admin_role(array('OPERATIONS'));
    }

    /** GET /admin/employees */
    public function index()
    {
        $search = trim((string) $this->input->get('search'));
        $page = max(1, (int) $this->input->get('page'));

        $result = $this->users->admin_list($search, 10, $page);

        $this->render('employees', array(
            'page_title' => 'Employees',
            'employees' => $result['data'],
            'pagination' => $result,
            'filters' => array('search' => $search),
            'roles' => $this->roles->all(array(), 'name ASC'),
            'branches' => $this->branches->all(array(), 'name ASC'),
        ));
    }

    /** POST /admin/employees/create */
    public function store()
    {
        $data = $this->collect_input();
        $errors = $this->validate_employee($data, null);

        if ($errors) {
            $this->session->set_flashdata('error', implode(' ', $errors));
            redirect('admin/employees');

            return;
        }

        $this->users->insert(array(
            'employee_code' => $data['employee_code'],
            'name' => $data['name'],
            'mobile' => $data['mobile'],
            'email' => $data['email'] !== '' ? $data['email'] : null,
            'password' => $this->users->hash_secret($data['password']),
            'role_id' => $data['role_id'],
            'branch_id' => $data['branch_id'] !== '' ? $data['branch_id'] : null,
            'is_active' => $data['is_active'] ? 1 : 0,
        ));

        $this->session->set_flashdata('status', 'Employee created.');
        redirect('admin/employees');
    }

    /** POST /admin/employees/(:num) */
    public function update($id)
    {
        $existing = $this->users->find($id);

        if (! $existing) {
            show_404();

            return;
        }

        $data = $this->collect_input();
        $errors = $this->validate_employee($data, $id);

        if ($errors) {
            $this->session->set_flashdata('error', implode(' ', $errors));
            redirect('admin/employees');

            return;
        }

        $update = array(
            'employee_code' => $data['employee_code'],
            'name' => $data['name'],
            'mobile' => $data['mobile'],
            'email' => $data['email'] !== '' ? $data['email'] : null,
            'role_id' => $data['role_id'],
            'branch_id' => $data['branch_id'] !== '' ? $data['branch_id'] : null,
            'is_active' => $data['is_active'] ? 1 : 0,
        );

        if ($data['password'] !== '') {
            $update['password'] = $this->users->hash_secret($data['password']);
        }

        $this->users->update($id, $update);

        $this->session->set_flashdata('status', 'Employee updated.');
        redirect('admin/employees');
    }

    /** POST /admin/employees/(:num)/toggle */
    public function toggle_active($id)
    {
        $user = $this->users->find($id);

        if (! $user) {
            show_404();

            return;
        }

        $this->users->update($id, array('is_active' => $user['is_active'] ? 0 : 1));

        $this->session->set_flashdata('status', 'Employee status updated.');
        redirect('admin/employees');
    }

    private function collect_input()
    {
        return array(
            'employee_code' => trim((string) $this->input->post('employee_code')),
            'name' => trim((string) $this->input->post('name')),
            'mobile' => trim((string) $this->input->post('mobile')),
            'email' => trim((string) $this->input->post('email')),
            'password' => (string) $this->input->post('password'),
            'role_id' => $this->input->post('role_id'),
            'branch_id' => (string) $this->input->post('branch_id'),
            'is_active' => $this->input->post('is_active') ? 1 : 0,
        );
    }

    /** Mirrors Employees\Index::rules() — required/unique checks, password optional on edit. */
    private function validate_employee($data, $ignore_id)
    {
        $errors = array();

        if ($data['employee_code'] === '') {
            $errors[] = 'Employee code is required.';
        } elseif (! $this->users->is_unique('employee_code', $data['employee_code'], $ignore_id)) {
            $errors[] = 'Employee code is already in use.';
        }

        if ($data['name'] === '') {
            $errors[] = 'Name is required.';
        }

        if ($data['mobile'] === '') {
            $errors[] = 'Mobile is required.';
        } elseif (! $this->users->is_unique('mobile', $data['mobile'], $ignore_id)) {
            $errors[] = 'Mobile is already in use.';
        }

        if ($data['email'] !== '' && ! $this->users->is_unique('email', $data['email'], $ignore_id)) {
            $errors[] = 'Email is already in use.';
        }

        if (! $ignore_id) {
            if ($data['password'] === '' || strlen($data['password']) < 8) {
                $errors[] = 'Password is required (minimum 8 characters).';
            }
        } elseif ($data['password'] !== '' && strlen($data['password']) < 8) {
            $errors[] = 'Password must be at least 8 characters.';
        }

        if (empty($data['role_id'])) {
            $errors[] = 'Role is required.';
        }

        return $errors;
    }
}
