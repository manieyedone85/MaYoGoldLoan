<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Mirrors app/Http/Controllers/Api/V1/SyncController.php.
 * NOTE: the Laravel source's docblock describes a conflict-resolution rule
 * (last-write-wins for non-financial fields; server-wins + manual-review
 * flag for financial fields), but neither uploadQueue() nor downloadDelta()
 * in the actual Laravel controller implement that logic yet — they only
 * insert into sync_queues / read loans+customers by updated_at. Ported
 * faithfully as-is (no invented conflict detection); Sync_conflict_log_model
 * is provided for when that logic is built out on either side.
 */
class Sync extends Api_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->require_auth();
        $this->require_device_binding();
        $this->load->model('Sync_queue_model', 'queue');
        $this->load->model('Loan_model', 'loans');
        $this->load->model('Customer_model', 'customers');
    }

    /** POST /api/v1/sync/upload-queue */
    public function upload_queue()
    {
        $data = $this->json_input();

        if (empty($data['entity_type']) || empty($data['payload']) || ! is_array($data['payload'])) {
            return json_error('entity_type and payload (array) are required.');
        }

        $id = $this->queue->insert(array(
            'user_id' => $this->user['id'],
            'entity_type' => $data['entity_type'],
            'payload' => $data['payload'],
            'status' => 'PENDING',
        ));

        $this->audit_log(
            $data['entity_type'],
            $data['payload']['id'] ?? $id,
            'SYNC_UPDATE',
            null,
            $data['payload']
        );

        return json_response(array('data' => $this->queue->find($id)), 201);
    }

    /** GET /api/v1/sync/download-delta?lastSyncTs= */
    public function download_delta()
    {
        $since = $this->input->get('lastSyncTs');
        $since = $since ?: date('Y-m-d H:i:s', strtotime('-1 day'));

        $loans = $this->loans->all(array('updated_at >=' => $since));
        $customers = $this->customers->all(array('updated_at >=' => $since));

        return json_response(array(
            'loans' => $loans,
            'customers' => $customers,
        ));
    }
}
