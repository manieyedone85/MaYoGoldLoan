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
    const MAX_FAILS_PER_ACCOUNT = 5;
    const MAX_FAILS_PER_IP = 30;
    const LOCK_WINDOW = 900; // seconds
    const DB_DOWN_MESSAGE = 'Unable to connect to the database right now. Please try again shortly or contact the administrator.';

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

        $this->load->helper('csrf');
        $this->output->set_output(inject_csrf_fields(
            $this->load->view('admin/login', array('error' => $this->session->flashdata('error')), true)
        ));
    }

    /** POST /admin/login */
    public function attempt_login()
    {
        $email = strtolower(trim((string) $this->input->post('email', true)));
        $password = (string) $this->input->post('password');

        // Input validation -- shown on the login page via the flash 'error'.
        if ($email === '' && $password === '') {
            return $this->login_error('Email and password are required.');
        }
        if ($email === '') {
            return $this->login_error('Email is required.');
        }
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->login_error('Enter a valid email address.');
        }
        if ($password === '') {
            return $this->login_error('Password is required.');
        }

        // Without a DB connection every lookup below would crash with a blank HTTP 500;
        // say so on the login page instead.
        if (! $this->db->conn_id) {
            return $this->login_error(self::DB_DOWN_MESSAGE);
        }

        // Brute-force protection: per account and per client IP, fixed 15-minute window.
        $this->load->library('throttle');
        $acct_key = 'admin_login:acct:' . $email;
        $ip_key = 'admin_login:ip:' . $this->input->ip_address();
        if ($this->throttle->blocked($acct_key, self::MAX_FAILS_PER_ACCOUNT, self::LOCK_WINDOW)
            || $this->throttle->blocked($ip_key, self::MAX_FAILS_PER_IP, self::LOCK_WINDOW)) {
            $wait = max($this->throttle->retry_after($acct_key, self::LOCK_WINDOW), $this->throttle->retry_after($ip_key, self::LOCK_WINDOW));
            $this->session->set_flashdata('error', 'Too many failed login attempts. Try again in ' . max(1, (int) ceil($wait / 60)) . ' minute(s).');
            redirect('admin/login');

            return;
        }

        try {
            $user = $this->users->find_by_email($email);
        } catch (Throwable $e) {
            log_message('error', 'Admin login lookup failed: ' . $e->getMessage());

            return $this->login_error(self::DB_DOWN_MESSAGE);
        }

        // Always run one bcrypt verification so response time doesn't reveal whether the email exists.
        if (! $user) {
            password_verify($password, '$2y$10$KCabpgd6nfIzx6PdjFHcgOEqpww.vs39qrm7rR1TWcXQSevoDWfla');
        }

        // validate user existence, password and active flag
        if (! $user || ! $this->users->verify_password($user, $password) || ! (! empty($user['is_active']))) {
            $this->throttle->hit($acct_key, self::LOCK_WINDOW);
            $this->throttle->hit($ip_key, self::LOCK_WINDOW);
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

        $this->throttle->clear($acct_key);

        // New session ID on privilege change: defeats session fixation.
        $this->session->sess_regenerate(true);
        $this->session->set_userdata('admin_user_id', $user['id']);
        redirect('admin/dashboard');
    }

    /** Flash a message on the login page and go back to it. */
    private function login_error($message)
    {
        $this->session->set_flashdata('error', $message);
        redirect('admin/login');
    }

    /** POST /admin/logout */
    public function logout()
    {
        $this->session->unset_userdata('admin_user_id');
        $this->session->sess_destroy();
        redirect('admin/login');
    }
}
