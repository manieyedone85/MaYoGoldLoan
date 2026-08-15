<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Mirrors app/Http/Controllers/Api/V1/GoldReleaseController.php.
 * Routes:
 *   POST /api/v1/loan/{loan}/gold-release/verify-id                -- auth + device binding only
 *   POST /api/v1/loan/goldrelease/{goldRelease}/capture-signature  -- auth + device binding only
 *   POST /api/v1/loan/goldrelease/{goldRelease}/complete           -- role:BRANCH_MANAGER
 */
class Gold_release extends Api_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Loan_model', 'loans');
        $this->load->model('Gold_release_model', 'gold_releases');
        $this->load->model('Jewellery_item_model', 'jewellery_items');
    }

    /** POST /api/v1/loan/{loan}/gold-release/verify-id */
    public function verify_id($loan_id)
    {
        $user = $this->require_auth();
        $this->require_device_binding();

        $loan = $this->loans->find($loan_id);
        if (! $loan) {
            return json_error('Loan not found.', 404);
        }

        $data = $this->json_input();

        if (empty($data['jewellery_item_id']) || ! $this->jewellery_items->find($data['jewellery_item_id'])) {
            return json_error('jewellery_item_id is required and must reference an existing jewellery item.');
        }
        if (empty($data['released_to'])) {
            return json_error('released_to is required.');
        }

        // Mirrors GoldRelease::firstOrCreate(['loan_id' => ..., 'jewellery_item_id' => ...], [...]).
        $release = $this->gold_releases->first(array(
            'loan_id' => $loan['id'],
            'jewellery_item_id' => $data['jewellery_item_id'],
        ));

        if (! $release) {
            $release_id = $this->gold_releases->insert(array(
                'loan_id' => $loan['id'],
                'jewellery_item_id' => $data['jewellery_item_id'],
                'released_by' => $user['id'],
                'released_to' => $data['released_to'],
                'status' => 'PENDING',
            ));
        } else {
            $release_id = $release['id'];
        }

        $this->gold_releases->update($release_id, array('id_proof_verified' => 1));

        return json_response(array('data' => $this->gold_releases->find($release_id)));
    }

    /** POST /api/v1/loan/goldrelease/{goldRelease}/capture-signature */
    public function capture_signature($gold_release_id)
    {
        $this->require_auth();
        $this->require_device_binding();

        $release = $this->gold_releases->find($gold_release_id);
        if (! $release) {
            return json_error('Gold release not found.', 404);
        }

        $this->gold_releases->update($release['id'], array('signature_captured' => 1));

        return json_response(array('data' => $this->gold_releases->find($release['id'])));
    }

    /**
     * POST /api/v1/loan/goldrelease/{goldRelease}/complete
     * Blocked unless all three checklist gates are true.
     */
    public function complete($gold_release_id)
    {
        $this->require_auth();
        $this->require_device_binding();
        $this->require_role(array('BRANCH_MANAGER','ADMIN'));

        $release = $this->gold_releases->find($gold_release_id);
        if (! $release) {
            return json_error('Gold release not found.', 404);
        }

        if (! $this->gold_releases->is_ready_for_release($release)) {
            return json_error('ID proof, signature, and photo must all be captured first.');
        }

        $this->db->trans_start();

        $released_at = date('Y-m-d H:i:s');

        $this->gold_releases->update($release['id'], array(
            'status' => 'RELEASED',
            'released_at' => $released_at,
        ));

        if (! empty($release['jewellery_item_id'])) {
            $this->jewellery_items->update($release['jewellery_item_id'], array('status' => 'RELEASED'));
        }

        $this->audit_log(
            'Loan',
            $release['loan_id'],
            'RELEASE',
            array('status' => $release['status']),
            array('status' => 'RELEASED', 'released_at' => $released_at, 'jewellery_item_id' => $release['jewellery_item_id'])
        );

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            return json_error('Gold release completion failed. Please retry.', 500);
        }

        return json_response(array('data' => $this->gold_releases->find($release['id'])));
    }
}
