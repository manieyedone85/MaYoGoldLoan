<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Session-based admin login/logout. Deliberately extends CI_Controller
 * (not Admin_Controller) since the login page itself must be reachable
 * without a session. Access is open to every staff role (everything except
 * the mobile-only CUSTOMER role) -- see Admin_Controller::ADMIN_ELIGIBLE_ROLES,
 * checked both here and in every Admin_Controller-based page.
 */
class Auth extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('User_model', 'users');
        $this->load->model('Role_model', 'roles');
    }

    /** GET /admin/login */
    public function login_form()
    {
        if ($this->session->userdata('admin_user_id')) {
            redirect('admin/dashboard');

            return;
        }

        $this->load->view('admin/login', array('error' => $this->session->flashdata('error')));
    }

    /** POST /admin/login */
    public function attempt_login()
    {
        $email = $this->input->post('email', true);
        $password = $this->input->post('password', true);

        $user = $this->users->find_by_email($email);
        //  echo "<pre>";
        //  echo password_verify($password, $user['password']);
        //  print_r($user);echo $user['is_active'];//die();

        // validate user existence, password and active flag
        if (! $user || ! $this->users->verify_password($user, $password) || ! (! empty($user['is_active']))) {
            $this->session->set_flashdata('error', 'Invalid credentials.');
            redirect('admin/login');

            return;
        }

        // guard against missing role_id in DB row
        $role_id = isset($user['role_id']) ? $user['role_id'] : null;
        if (! $role_id) {
            $this->session->set_flashdata('error', 'User role misconfigured. Contact administrator.');
            redirect('admin/login');

            return;
        }

        $role = $this->roles->find($role_id);

        if (! $role || ! in_array($role['code'], Admin_Controller::ADMIN_ELIGIBLE_ROLES, true)) {
            $this->session->set_flashdata('error', 'You do not have access to the admin panel.');
            redirect('admin/login');

            return;
        }

        $this->session->set_userdata('admin_user_id', $user['id']);
        redirect('admin/dashboard');
    }

    /** POST /admin/logout */
    public function logout()
    {
        $this->session->unset_userdata('admin_user_id');
        $this->session->sess_destroy();
        redirect('admin/login');
    }
}
