<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Mirrors app/Http/Controllers/Api/V1/AuthController.php from the Laravel
 * app. Public: login, otp/send, otp/verify. Everything else requires a
 * valid Bearer token (require_auth) + device binding (require_device_binding).
 */
class Auth extends Api_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('User_model', 'users');
        $this->load->model('User_otp_model', 'otps');
        $this->load->model('User_device_binding_model', 'device_bindings');
    }

    /** POST /api/v1/auth/login */
    public function login()
    {
        $data = $this->json_input();
        if (empty($data['mobile']) || (empty($data['password']) && empty($data['mpin'])) || empty($data['device_id'])) {
            return json_error('mobile, device_id, and either password or mpin are required.');
        }

        $user = $this->users->find_by_mobile($data['mobile']);

        if (! $user || ! $user['is_active']) {
            return json_error('Invalid credentials.', 401);
        }

        $ok = false;
        if (! empty($data['password'])) {
            $ok = $this->users->verify_password($user, $data['password']);
        } elseif (! empty($data['mpin'])) {
            $ok = $this->users->verify_mpin($user, $data['mpin']);
        }

        if (! $ok) {
            return json_error('Invalid credentials.', 401);
        }

        $this->bind_device($user['id'], $data['device_id'], $data);
        $issued = $this->token_auth->issue($user['id'], 'mobile-app');
        $this->users->update($user['id'], array('last_login_at' => date('Y-m-d H:i:s')));

        $this->load->model('Role_model', 'roles');
        $role = $this->roles->find($user['role_id']);

        $this->audit_log('User', $user['id'], 'LOGIN', null, array(
            'role' => $role ? $role['code'] : null,
            'device_id' => $data['device_id'],
        ));

        return json_response(array(
            'token' => $issued['token'],
            'user' => array(
                'id' => $user['id'],
                'name' => $user['name'],
                'role' => $role ? $role['code'] : null,
                'branch_id' => $user['branch_id'],
            ),
        ));
    }

    /** POST /api/v1/auth/otp/send */
    public function send_otp()
    {
        $data = $this->json_input();

        if (empty($data['mobile']) || empty($data['purpose'])) {
            return json_error('mobile and purpose are required.');
        }

        $otp = (string) random_int(100000, 999999);

        $this->otps->insert(array(
            'mobile' => $data['mobile'],
            'otp_hash' => password_hash($otp, PASSWORD_BCRYPT),
            'purpose' => $data['purpose'],
            'expires_at' => date('Y-m-d H:i:s', time() + 300),
        ));

        // Dispatch via the notification gateway (SMS/WhatsApp) with retry/backoff.
        // Notification_lib::send_otp($data['mobile'], $otp);

        return json_response(array('message' => 'OTP sent.','otp' => $otp));
    }

    /** POST /api/v1/auth/otp/verify */
    public function verify_otp()
    {
        $data = $this->json_input();

        if (empty($data['mobile']) || empty($data['purpose']) || empty($data['otp'])) {
            return json_error('mobile, purpose, and otp are required.');
        }

        $record = $this->otps->latest_pending($data['mobile'], $data['purpose']);

        if (! $record || ! password_verify($data['otp'], $record['otp_hash'])) {
            return json_error('Invalid or expired OTP.', 422);
        }

        $this->otps->update($record['id'], array('is_verified' => 1));

        if ($data['purpose'] === 'LOGIN') {
            $user = $this->users->find_by_mobile($data['mobile']);
            if (! $user) {
                return json_error('User not found.', 404);
            }
            if (! $user['is_active']) {
                return json_error('Account is inactive.', 403);
            }

            if (! empty($data['device_id'])) {
                $this->bind_device($user['id'], $data['device_id'], $data);
            }

            $issued = $this->token_auth->issue($user['id'], 'mobile-app');

            $this->audit_log('User', $user['id'], 'OTP_LOGIN', null, array(
                'device_id' => $data['device_id'] ?? null,
            ));

            return json_response(array('token' => $issued['token']));
        }

        return json_response(array('message' => 'OTP verified.', 'reset_token' => bin2hex(random_bytes(20))));
    }

    /** POST /api/v1/auth/refresh-token */
    public function refresh_token()
    {
        $user = $this->require_auth();
        $this->require_device_binding();

        $header = $this->input->get_request_header('Authorization', TRUE);
        $old_token = trim(substr($header, 7));
        $this->token_auth->revoke($old_token);

        $issued = $this->token_auth->issue($user['id'], 'mobile-app');

        $this->audit_log('User', $user['id'], 'TOKEN_REFRESH');

        return json_response(array('token' => $issued['token']));
    }

    /** POST /api/v1/auth/logout */
    public function logout()
    {
        $this->require_auth();
        $this->require_device_binding();

        $header = $this->input->get_request_header('Authorization', TRUE);

        $this->audit_log('User', $this->user['id'], 'LOGOUT');

        $this->token_auth->revoke(trim(substr($header, 7)));

        return json_response(array('message' => 'Logged out.'));
    }

    /** POST /api/v1/auth/device/bind */
    public function bind_device_endpoint()
    {
        $user = $this->require_auth();
        $this->require_device_binding();

        $data = $this->json_input();
        if (empty($data['device_id'])) {
            return json_error('device_id is required.');
        }

        $this->bind_device($user['id'], $data['device_id'], $data);

        $this->audit_log('User', $user['id'], 'DEVICE_BIND', null, array(
            'device_id' => $data['device_id'],
        ));

        return json_response(array('message' => 'Device bound.'));
    }

    /** POST /api/v1/auth/mpin/set */
    public function set_mpin()
    {
        $user = $this->require_auth();
        $this->require_device_binding();

        $data = $this->json_input();
        if (empty($data['mpin']) || strlen((string) $data['mpin']) !== 6) {
            return json_error('mpin must be exactly 6 characters.');
        }

        $this->users->update($user['id'], array('mpin_hash' => password_hash($data['mpin'], PASSWORD_BCRYPT)));

        $this->audit_log('User', $user['id'], 'MPIN_SET', null, array(
            'mpin_set_at' => date('Y-m-d H:i:s'),
        ));

        return json_response(array('message' => 'MPIN set.'));
    }

    private function bind_device($user_id, $device_id, array $extra = array())
    {
        $existing = $this->device_bindings->first(array('user_id' => $user_id, 'device_id' => $device_id));
        $payload = array(
            'device_model' => $extra['device_model'] ?? null,
            'push_token' => $extra['push_token'] ?? null,
            'is_active' => 1,
            'bound_at' => date('Y-m-d H:i:s'),
        );

        if ($existing) {
            $this->device_bindings->update($existing['id'], $payload);
        } else {
            $payload['user_id'] = $user_id;
            $payload['device_id'] = $device_id;
            $this->device_bindings->insert($payload);
        }
    }
}
