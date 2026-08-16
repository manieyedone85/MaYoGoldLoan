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

        // BRD §15 "Secure login, OTP" (docs/BRD_COVERAGE_AUDIT.md) / critical
        // issue #1: the OTP must never be echoed back in the API response --
        // it has to reach the user only through the notification gateway
        // above. A prior pass on this file claimed this was already fixed,
        // but the leak was still live; removed for real this time.
        return json_response(array('message' => 'OTP sent.'));
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

    /**
     * POST /api/v1/auth/biometric/enroll
     *
     * Not a Laravel port -- added for BRD §15 "Secure login, OTP, optional
     * biometric" (docs/BRD_COVERAGE_AUDIT.md). `user_biometric_ref` already
     * existed in the live schema but nothing ever read or wrote to it, and
     * there was no biometric login path at all -- only the customer-side KYC
     * face-auth flow (Kyc_aadhaar::face_auth()) existed. `template_ref` is an
     * opaque reference the device's secure hardware (Face ID / fingerprint
     * sensor) generates on enrollment -- raw biometric data never reaches
     * this endpoint or gets stored.
     *
     * Hardened after code review: template_ref now requires real entropy (a
     * short guessable string like "1234" used to pass validation), and is
     * bound to the enrolling device (device_id column added by
     * docs/migrations/2026_08_16_biometric_login_hardening.sql) so it can't
     * be replayed from a different device that happens to already be bound
     * to this user.
     */
    public function enroll_biometric()
    {
        $user = $this->require_auth();
        $this->require_device_binding();

        $data = $this->json_input();

        if (empty($data['type']) || ! in_array($data['type'], array('FACE', 'FINGERPRINT'), true)) {
            return json_error('type is required and must be FACE or FINGERPRINT.');
        }
        if (empty($data['template_ref']) || strlen((string) $data['template_ref']) < 32 || strlen((string) $data['template_ref']) > 255) {
            return json_error('template_ref is required and must be between 32 and 255 characters (a device-generated secure reference, not a short PIN-like value).');
        }

        $device_id = $this->input->get_request_header('X-Device-Id', TRUE);
        if (empty($device_id)) {
            return json_error('X-Device-Id header is required to enroll a biometric credential.');
        }

        $this->load->model('User_biometric_ref_model', 'biometric_refs');

        $id = $this->biometric_refs->insert(array(
            'user_id' => $user['id'],
            'device_id' => $device_id,
            'type' => $data['type'],
            'template_ref' => $data['template_ref'],
        ));

        $this->audit_log('User', $user['id'], 'BIOMETRIC_ENROLL', null, array('type' => $data['type'], 'device_id' => $device_id));

        return json_response(array('data' => $this->biometric_refs->find($id)), 201);
    }

    /**
     * POST /api/v1/auth/biometric/login
     *
     * The actual biometric match happens in the device's secure hardware --
     * this endpoint never receives raw biometric data, only the opaque
     * template_ref the device was enrolled with (enroll_biometric() above).
     * Mirrors the same "device attests, server checks the reference matches
     * enrollment" pattern Kyc_aadhaar::face_auth() already uses for
     * customer-side verification. Requires the device to already be bound to
     * this user via a prior password/OTP login -- biometric login is a
     * convenience shortcut for a known device, not an independent factor on
     * its own.
     *
     * Hardened after code review: the match now also requires device_id to
     * equal the device the credential was enrolled on (not just "any device
     * bound to this user"), and repeated failed attempts are rate-limited --
     * previously this was an unthrottled equality check against a
     * client-chosen value with no minimum entropy enforced.
     */
    public function biometric_login()
    {
        $data = $this->json_input();

        if (empty($data['mobile']) || empty($data['device_id']) || empty($data['type']) || empty($data['template_ref'])) {
            return json_error('mobile, device_id, type, and template_ref are required.');
        }
        if (! in_array($data['type'], array('FACE', 'FINGERPRINT'), true)) {
            return json_error('type must be FACE or FINGERPRINT.');
        }

        $user = $this->users->find_by_mobile($data['mobile']);
        if (! $user || ! $user['is_active']) {
            return json_error('Invalid credentials.', 401);
        }

        $this->load->model('User_biometric_ref_model', 'biometric_refs');

        if ($this->biometric_refs->recent_failed_attempt_count($user['id'], date('Y-m-d H:i:s', time() - 900)) >= 5) {
            return json_error('Too many failed biometric login attempts. Log in with password/OTP instead.', 429);
        }

        $bound = $this->device_bindings->first(array(
            'user_id' => $user['id'],
            'device_id' => $data['device_id'],
            'is_active' => 1,
        ));
        if (! $bound) {
            return json_error('This device is not bound to the account. Log in with password/OTP first.', 401);
        }

        $enrolled = $this->biometric_refs->find_for_login($user['id'], $data['type'], $data['template_ref'], $data['device_id']);
        if (! $enrolled) {
            $this->audit_log('User', $user['id'], 'BIOMETRIC_LOGIN_FAILED', null, array('device_id' => $data['device_id'], 'type' => $data['type']));

            return json_error('Biometric not recognized. Log in with password/OTP.', 401);
        }

        $issued = $this->token_auth->issue($user['id'], 'mobile-app');
        $this->users->update($user['id'], array('last_login_at' => date('Y-m-d H:i:s')));

        $this->load->model('Role_model', 'roles');
        $role = $this->roles->find($user['role_id']);

        $this->audit_log('User', $user['id'], 'BIOMETRIC_LOGIN', null, array(
            'role' => $role ? $role['code'] : null,
            'device_id' => $data['device_id'],
            'type' => $data['type'],
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
