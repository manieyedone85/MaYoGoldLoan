<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Base controller for every API v1 controller (controllers/api/v1/*).
 * Mirrors the Laravel API's `auth:sanctum` -> `device.binding` -> `role:*`
 * middleware chain, but as explicit method calls each controller invokes
 * from its constructor/method, since CI3 has no middleware pipeline.
 *
 *   class Loan extends Api_Controller {
 *       public function submit_for_approval($loan_id) {
 *           $this->require_auth();
 *           $this->require_role(['BRANCH_EXECUTIVE']);
 *           ...
 *       }
 *   }
 */
class Api_Controller extends CI_Controller
{
    /** @var array|null authenticated user row, set by require_auth() */
    protected $user;

    public function __construct()
    {
        parent::__construct();
        $this->load->library('Token_auth');
        $this->load->helper('response');
    }

    /**
     * Validates the Bearer token and halts the request with 401 if missing/
     * invalid/expired. Sets $this->user on success.
     */
    protected function require_auth()
    {
        $user = $this->token_auth->authenticate();

        if (! $user) {
            json_error('Unauthenticated.', 401);
            exit;
        }

        if (! $user['is_active']) {
            json_error('Account is inactive.', 403);
            exit;
        }

        $this->user = $user;

        return $user;
    }

    /**
     * Equivalent of `role:ROLE_A,ROLE_B` middleware. Call after require_auth().
     */
    protected function require_role(array $role_codes)
    {
        $this->load->model('Role_model', 'roles');
        $role = $this->roles->find($this->user['role_id']);

        if (! $role || ! in_array($role['code'], $role_codes, true)) {
            json_error('You do not have permission to perform this action.', 403);
            exit;
        }

        return $role;
    }

    /**
     * Equivalent of the `device.binding` middleware (App\Http\Middleware\
     * DeviceBindingCheck): single-device enforcement, but only for
     * BRANCH_EXECUTIVE / APPRAISER / CASHIER roles, and only a *conflict*
     * check — it never requires the header to pre-exist or match an
     * already-registered device, so first-time binding and non-restricted
     * roles pass straight through.
     */
    protected function require_device_binding()
    {
        $single_device_roles = array('BRANCH_EXECUTIVE', 'APPRAISER', 'CASHIER');

        $this->load->model('Role_model', 'roles');
        $role = $this->roles->find($this->user['role_id']);
        $role_code = $role ? $role['code'] : null;

        if (! in_array($role_code, $single_device_roles, true)) {
            return;
        }

        $device_id = $this->input->get_request_header('X-Device-Id', TRUE);

        if (! $device_id) {
            return;
        }

        $this->load->model('User_device_binding_model', 'device_bindings');
        $bound = $this->device_bindings->first(array(
            'user_id' => $this->user['id'],
            'is_active' => 1,
        ));

        if ($bound && $bound['device_id'] !== $device_id) {
            json_error('This account is bound to a different device. Contact your manager to rebind.', 409);
            exit;
        }
    }

    /** Parses the JSON request body into an assoc array (POST/PUT/PATCH). */
    protected function json_input()
    {
        $raw = $this->input->raw_input_stream;

        return $raw ? (json_decode($raw, true) ?: array()) : $this->input->post(null, true);
    }

    /**
     * Writes one row to `audit_logs` (entity_type/entity_id/action/before_value/
     * after_value/actor_id). Call this after a state-changing write succeeds —
     * never before, so a rejected/validation-failed request leaves no entry.
     * Actor defaults to $this->user (set by require_auth()); pass null before_value
     * for CREATE actions. Never throws: a logging failure must not fail the request.
     */
    protected function audit_log($entity_type, $entity_id, $action, $before = null, $after = null)
    {
        try {
            $this->load->model('Audit_log_model', 'audit_logs');
            $this->audit_logs->insert(array(
                'entity_type' => $entity_type,
                'entity_id' => $entity_id,
                'action' => $action,
                'before_value' => $before,
                'after_value' => $after,
                'actor_id' => $this->user['id'] ?? null,
            ));
        } catch (Exception $e) {
            log_message('error', 'audit_log failed for ' . $entity_type . '#' . $entity_id . '/' . $action . ': ' . $e->getMessage());
        }
    }
}

/**
 * Base controller for every admin panel controller (controllers/admin/*).
 * Mirrors the Laravel admin panel's session `auth` + `role:ADMIN,OPERATIONS`
 * middleware. Views are loaded through render() so every page shares the
 * same Bootstrap layout, current user, and flash message handling.
 */
class Admin_Controller extends CI_Controller
{
    protected $user;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('User_model', 'users');
        $this->load->model('Role_model', 'roles');
        $this->load->helper('url');

        $this->require_admin_session();
    }

    protected function require_admin_session()
    {
        $user_id = $this->session->userdata('admin_user_id');

        if (! $user_id) {
            redirect('admin/login');

            return;
        }

        $user = $this->users->find($user_id);
        $role = $user ? $this->roles->find($user['role_id']) : null;

        if (! $user || ! $user['is_active'] || ! $role || ! in_array($role['code'], array('ADMIN', 'OPERATIONS'), true)) {
            $this->session->sess_destroy();
            redirect('admin/login');

            return;
        }

        $this->user = $user;
        $this->user['role_name'] = $role['name'];
    }

    /**
     * Renders `views/admin/{$view}.php` inside the shared admin layout
     * (`views/admin/_layout.php`), passing $data plus the current user.
     */
    protected function render($view, $data = array())
    {
        $layout_data = array(
            'current_user' => $this->user,
            'content_view' => 'admin/' . $view,
            'page_title' => $data['page_title'] ?? 'Admin',
            'flash' => $this->session->flashdata('status'),
            'view_data' => $data,
        );

        $this->load->view('admin/_layout', $layout_data);
    }
}
