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

        $new_id = $this->users->insert(array(
            'employee_code' => $data['employee_code'],
            'name' => $data['name'],
            'mobile' => $data['mobile'],
            'email' => $data['email'] !== '' ? $data['email'] : null,
            'password' => $this->users->hash_secret($data['password']),
            'role_id' => $data['role_id'],
            'branch_id' => $data['branch_id'] !== '' ? $data['branch_id'] : null,
            'is_active' => $data['is_active'] ? 1 : 0,
        ));

        $this->audit_log('User', $new_id, 'EMPLOYEE_CREATE', null, $this->audit_snapshot($this->users->find($new_id)));

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

        if (! $this->can_manage($existing)) {
            return $this->deny_admin_target();
        }

        $data = $this->collect_input();
        $errors = $this->validate_employee($data, $id);

        // Nobody may change their own role or deactivate themselves from this screen.
        if ((int) $id === (int) $this->user['id']) {
            if ((int) $data['role_id'] !== (int) $existing['role_id']) {
                $errors[] = 'You cannot change your own role.';
            }
            if (! $data['is_active']) {
                $errors[] = 'You cannot deactivate your own account.';
            }
        }

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

        $this->audit_log('User', $id, 'EMPLOYEE_UPDATE', $this->audit_snapshot($existing), $this->audit_snapshot($this->users->find($id)) + ($data['password'] !== '' ? array('password_reset' => true) : array()));

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

        if (! $this->can_manage($user)) {
            return $this->deny_admin_target();
        }
        if ((int) $id === (int) $this->user['id']) {
            $this->session->set_flashdata('error', 'You cannot deactivate your own account.');
            redirect('admin/employees');

            return;
        }

        $this->users->update($id, array('is_active' => $user['is_active'] ? 0 : 1));

        $this->audit_log('User', $id, 'EMPLOYEE_TOGGLE_ACTIVE', array('is_active' => (int) $user['is_active']), array('is_active' => $user['is_active'] ? 0 : 1));

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
        } else {
            $role = $this->roles->find((int) $data['role_id']);
            if (! $role) {
                $errors[] = 'Selected role does not exist.';
            } elseif ($role['code'] === 'ADMIN' && $this->user['role_code'] !== 'ADMIN') {
                // Only an ADMIN may mint another ADMIN -- otherwise OPERATIONS could self-escalate.
                $errors[] = 'Only an administrator can assign the ADMIN role.';
            }
        }

        if ($data['branch_id'] !== '' && ! $this->branches->find((int) $data['branch_id'])) {
            $errors[] = 'Selected branch does not exist.';
        }

        return $errors;
    }

    /**
     * ADMIN accounts (and super-admin accounts) may only be edited, reset or
     * (de)activated by an ADMIN (super admins only by a super admin) -- a
     * lower role with access to this screen must not be able to take over a
     * more privileged account by resetting its password.
     */
    private function can_manage($target)
    {
        if (! empty($target['is_super_admin']) && empty($this->user['is_super_admin'])) {
            return false;
        }

        $target_role = $this->roles->find($target['role_id']);
        if ($target_role && $target_role['code'] === 'ADMIN' && $this->user['role_code'] !== 'ADMIN') {
            return false;
        }

        return true;
    }

    private function deny_admin_target()
    {
        $this->session->set_flashdata('error', 'You are not allowed to modify administrator accounts.');
        redirect('admin/employees');
    }

    /** Audit-log payload for a user row: never includes password/MPIN hashes. */
    private function audit_snapshot($user)
    {
        unset($user['password'], $user['mpin_hash'], $user['remember_token']);

        return $user;
    }
}
