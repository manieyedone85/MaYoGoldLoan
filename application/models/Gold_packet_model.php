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
}
