<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Admin panel: Inventory (gold packets & vault custody). Ports
 * application/controllers/api/v1/Inventory.php's store()/transfer()/
 * vault_status() -- same packet-code generation, same transfer-log +
 * vault_id update pair. Vault master CRUD lives in admin/Masters.php
 * (pure config), this screen is packet tracking/transfer.
 *
 * Named `Inventories` (not `Inventory`) purely to avoid declaring a second
 * PHP class literally named `Inventory` alongside
 * `api/v1/Inventory.php`'s `class Inventory extends Api_Controller` --
 * same reasoning behind every other admin controller in this codebase being
 * pluralized against its singular api/v1 counterpart (Loans vs Loan,
 * Renewals vs Renewal, etc.). The URL stays `admin/inventory` either way
 * (see routes.php); this is only the controller class/file name.
 */
class Inventories extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        // No role restriction beyond admin-panel login, matching the API
        // (Inventory extends Api_Controller with only require_auth() in its
        // own constructor -- no require_role() anywhere in that file).

        $this->load->model('Gold_packet_model', 'gold_packets');
        $this->load->model('Packet_transfer_log_model', 'transfer_logs');
        $this->load->model('Vault_model', 'vaults');
        $this->load->model('Branch_model', 'branches');
        $this->load->model('Jewellery_item_model', 'jewellery_items');
    }

    /** GET /admin/inventory */
    public function index()
    {
        $branch_id = $this->input->get('branch_id');
        $search = trim((string) $this->input->get('search'));
        $page = max(1, (int) $this->input->get('page'));

        $result = $this->gold_packets->with_relations($search, 15, $page);

        $this->render('inventory', array(
            'page_title' => 'Inventory',
            'packets' => $result['data'],
            'pagination' => $result,
            'filters' => array('search' => $search, 'branch_id' => $branch_id),
            'vaults' => $this->vaults->all(array(), 'name ASC'),
            'branches' => $this->branches->all(array(), 'name ASC'),
            'branch_id' => $branch_id,
            'vault_status' => $branch_id ? $this->gold_packets->status_counts_for_branch($branch_id) : array(),
        ));
    }

    /** POST /admin/inventory/packet */
    public function store()
    {
        $jewellery_item_id = $this->input->post('jewellery_item_id');
        if (! $jewellery_item_id || ! $this->jewellery_items->find($jewellery_item_id)) {
            return $this->_fail('A valid jewellery item is required.');
        }

        $vault_id = $this->input->post('vault_id');
        if (! $vault_id || ! $this->vaults->find($vault_id)) {
            return $this->_fail('A valid vault is required.');
        }

        $id = $this->gold_packets->insert(array(
            'jewellery_item_id' => $jewellery_item_id,
            'vault_id' => $vault_id,
            'packet_code' => $this->gold_packets->next_packet_code(),
            'status' => 'IN_VAULT',
        ));

        $this->audit_log('GoldPacket', $id, 'CREATE', null, array('jewellery_item_id' => $jewellery_item_id, 'vault_id' => $vault_id, 'status' => 'IN_VAULT'));

        $this->session->set_flashdata('status', 'Gold packet created.');
        redirect('admin/inventory');
    }

    /** POST /admin/inventory/(:num)/transfer */
    public function transfer($gold_packet_id)
    {
        $packet = $this->gold_packets->find($gold_packet_id);
        if (! $packet) {
            show_404();

            return;
        }

        $to_vault_id = $this->input->post('to_vault_id');
        if (! $to_vault_id || ! $this->vaults->find($to_vault_id)) {
            return $this->_fail('A valid destination vault is required.');
        }

        $transfer_id = $this->transfer_logs->insert(array(
            'gold_packet_id' => $packet['id'],
            'from_vault_id' => $packet['vault_id'],
            'to_vault_id' => $to_vault_id,
            'transferred_by' => $this->user['id'],
        ));

        $this->gold_packets->update($packet['id'], array('vault_id' => $to_vault_id));

        $this->audit_log('InventoryTransfer', $transfer_id, 'TRANSFER',
            array('vault_id' => $packet['vault_id']),
            array('vault_id' => $to_vault_id, 'gold_packet_id' => $packet['id'])
        );

        $this->session->set_flashdata('status', 'Packet ' . $packet['packet_code'] . ' transferred.');
        redirect('admin/inventory');
    }

    private function _fail($message)
    {
        $this->session->set_flashdata('error', $message);
        redirect('admin/inventory');
    }
}
