<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Gold_packet_model extends MY_Model
{
    protected $table = 'gold_packets';

    public function next_packet_code()
    {
        $max_id = (int) $this->db->select_max('id')->get($this->table)->row('id');

        return 'PKT' . str_pad((string) ($max_id + 1), 8, '0', STR_PAD_LEFT);
    }

    /**
     * Counts of gold_packets grouped by status for all packets whose vault
     * belongs to the given branch (mirrors GoldPacket::whereHas('vault', ...)).
     */
    public function status_counts_for_branch($branch_id)
    {
        return $this->db->select('gold_packets.status, COUNT(*) AS total')
            ->from('gold_packets')
            ->join('vaults', 'vaults.id = gold_packets.vault_id')
            ->where('vaults.branch_id', $branch_id)
            ->group_by('gold_packets.status')
            ->get()
            ->result_array();
    }

    /** Packets joined with jewellery barcode and vault/branch names -- for the admin Inventory list. */
    public function with_relations($limit = 100)
    {
        return $this->db->select('gold_packets.*, jewellery_items.barcode, vaults.name AS vault_name, branches.name AS branch_name')
            ->from('gold_packets')
            ->join('jewellery_items', 'jewellery_items.id = gold_packets.jewellery_item_id', 'left')
            ->join('vaults', 'vaults.id = gold_packets.vault_id', 'left')
            ->join('branches', 'branches.id = vaults.branch_id', 'left')
            ->order_by('gold_packets.id', 'DESC')
            ->limit($limit)
            ->get()
            ->result_array();
    }
}
