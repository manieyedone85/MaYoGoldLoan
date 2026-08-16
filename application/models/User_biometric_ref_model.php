<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Staff biometric enrollment (user_biometric_ref table) -- distinct from
 * customer-side KYC face-auth (Kyc_face_auth_log_model). Added for BRD §15
 * "Secure login, OTP, optional biometric" (docs/BRD_COVERAGE_AUDIT.md):
 * the table already existed in the live schema but nothing ever read or
 * wrote to it, and there was no biometric login path at all.
 *
 * $timestamps is off because this table only has `created_at`, no
 * `updated_at` (same pattern as Customer_duplicate_log_model).
 */
class User_biometric_ref_model extends MY_Model
{
    protected $table = 'user_biometric_ref';
    protected $timestamps = false;

    public function insert($data)
    {
        $data['created_at'] = $data['created_at'] ?? date('Y-m-d H:i:s');

        $this->db->insert($this->table, $data);

        return $this->db->insert_id();
    }

    /**
     * device_id is included so a template_ref can only authenticate from the
     * exact device it was enrolled on -- without this, a leaked/guessed
     * template_ref could be replayed from any device already bound to the
     * user (docs/migrations/2026_08_16_biometric_login_hardening.sql).
     */
    public function find_for_login($user_id, $type, $template_ref, $device_id)
    {
        return $this->first(array(
            'user_id' => $user_id,
            'type' => $type,
            'template_ref' => $template_ref,
            'device_id' => $device_id,
        ));
    }

    public function recent_failed_attempt_count($user_id, $since)
    {
        return $this->db->from('audit_logs')
            ->where('entity_type', 'User')
            ->where('entity_id', $user_id)
            ->where('action', 'BIOMETRIC_LOGIN_FAILED')
            ->where('created_at >=', $since)
            ->count_all_results();
    }
}
