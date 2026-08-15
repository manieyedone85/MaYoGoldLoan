<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Minimal DB-backed bearer token auth, filling in for Laravel Sanctum
 * (which CodeIgniter 3 has no equivalent of). Reuses the existing
 * `personal_access_tokens` table as-is:
 *   - issue(): creates a row, returns the ONE-TIME plaintext token to the
 *     client. Only its SHA-256 hash is ever stored.
 *   - authenticate(): reads the `Authorization: Bearer <token>` header,
 *     hashes it, looks up a non-expired row, and returns the matching user.
 *
 * Token TTL mirrors the Laravel app's JWT_TTL_MINUTES (default 60 min);
 * override via CI_TOKEN_TTL_MINUTES env var.
 */
class Token_auth
{
    protected $ci;

    public function __construct()
    {
        $this->ci =& get_instance();
        $this->ci->load->model('Personal_access_token_model', 'tokens');
        $this->ci->load->model('User_model', 'users');
    }

    /**
     * @return array{token: string, expires_at: string}
     */
    public function issue($user_id, $name = 'api')
    {
        $plainText = bin2hex(random_bytes(32));
        $ttlMinutes = (int) (getenv('CI_TOKEN_TTL_MINUTES') ?: 60);
        $expiresAt = date('Y-m-d H:i:s', time() + ($ttlMinutes * 60));

        $this->ci->tokens->insert(array(
            'tokenable_type' => 'User',
            'tokenable_id' => $user_id,
            'name' => $name,
            'token' => hash('sha256', $plainText),
            'abilities' => null,
            'expires_at' => $expiresAt,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ));

        return array('token' => $plainText, 'expires_at' => $expiresAt);
    }

    public function revoke($plainText)
    {
        $row = $this->find_row($plainText);
        if ($row) {
            $this->ci->tokens->delete($row['id']);
        }
    }

    /**
     * @return array|null the authenticated user row, or null if the
     *                     Authorization header is missing/invalid/expired.
     */
    public function authenticate()
    {
        $header = $this->ci->input->get_request_header('Authorization', TRUE);

        if (! $header || stripos($header, 'Bearer ') !== 0) {
            return null;
        }

        $plainText = trim(substr($header, 7));
        $row = $this->find_row($plainText);

        if (! $row) {
            return null;
        }

        if ($row['expires_at'] && strtotime($row['expires_at']) < time()) {
            return null;
        }

        $this->ci->tokens->update($row['id'], array(
            'last_used_at' => date('Y-m-d H:i:s'),
        ));

        return $this->ci->users->find($row['tokenable_id']);
    }

    protected function find_row($plainText)
    {
        return $this->ci->tokens->first(array('token' => hash('sha256', $plainText)));
    }
}
