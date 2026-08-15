<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Mirrors app/Http/Controllers/Api/V1/InventoryController.php.
 * No role: middleware on this group in the Laravel route file — only
 * auth:sanctum + device.binding (a no-op here since none of the roles
 * hitting this controller are in the single-device list).
 */
class Inventory extends Api_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->require_auth();

        $this->load->model('Gold_packet_model', 'gold_packets');
        $this->load->model('Packet_transfer_log_model', 'transfer_logs');
        $this->load->model('Vault_model', 'vaults');
    }

    /** POST /api/v1/inventory/packet */
    public function store()
    {
        $data = $this->json_input();

        if (empty($data['jewellery_item_id']) || ! $this->db->where('id', $data['jewellery_item_id'])->get('jewellery_items')->row_array()) {
            return json_error('jewellery_item_id is required and must exist.');
        }
        if (empty($data['vault_id']) || ! $this->vaults->find($data['vault_id'])) {
            return json_error('vault_id is required and must exist.');
        }

        $id = $this->gold_packets->insert(array(
            'jewellery_item_id' => $data['jewellery_item_id'],
            'vault_id' => $data['vault_id'],
            'packet_code' => $this->gold_packets->next_packet_code(),
            'status' => 'IN_VAULT',
        ));

        $this->audit_log('GoldPacket', $id, 'CREATE', null, array(
            'jewellery_item_id' => $data['jewellery_item_id'],
            'vault_id' => $data['vault_id'],
            'status' => 'IN_VAULT',
        ));

        return json_response(array('data' => $this->gold_packets->find($id)), 201);
    }

    /**
     * POST /api/v1/inventory/packet/{id}/transfer
     * State machine: IN_VAULT -> PLEDGED -> IN_VAULT (on closure) ->
     * AUCTION_ELIGIBLE -> AUCTIONED/RELEASED.
     */
    public function transfer($gold_packet_id)
    {
        $packet = $this->gold_packets->find($gold_packet_id);
        if (! $packet) {
            return json_error('Gold packet not found.', 404);
        }

        $data = $this->json_input();

        if (empty($data['to_vault_id']) || ! $this->vaults->find($data['to_vault_id'])) {
            return json_error('to_vault_id is required and must exist.');
        }

        $transfer_id = $this->transfer_logs->insert(array(
            'gold_packet_id' => $packet['id'],
            'from_vault_id' => $packet['vault_id'],
            'to_vault_id' => $data['to_vault_id'],
            'transferred_by' => $this->user['id'],
        ));

        $this->gold_packets->update($packet['id'], array('vault_id' => $data['to_vault_id']));

        $this->audit_log(
            'InventoryTransfer',
            $transfer_id,
            'TRANSFER',
            array('vault_id' => $packet['vault_id']),
            array('vault_id' => $data['to_vault_id'], 'gold_packet_id' => $packet['id'])
        );

        return json_response(array('data' => $this->gold_packets->find($gold_packet_id)));
    }

    /** GET /api/v1/inventory/vault/{branchId}/status */
    public function vault_status($branch_id)
    {
        return json_response(array('data' => $this->gold_packets->status_counts_for_branch($branch_id)));
    }
}
