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

    /**
     * Packets joined with jewellery barcode and vault/branch names, paginated
     * -- for the admin Inventory list. Optional search matches packet code,
     * jewellery barcode, vault name, or branch name.
     */
    public function with_relations($search = '', $per_page = 15, $page = 1)
    {
        $build = function () use ($search) {
            $query = $this->db->from('gold_packets')
                ->join('jewellery_items', 'jewellery_items.id = gold_packets.jewellery_item_id', 'left')
                ->join('vaults', 'vaults.id = gold_packets.vault_id', 'left')
                ->join('branches', 'branches.id = vaults.branch_id', 'left');

            if ($search !== '') {
                $query->group_start()
                    ->like('gold_packets.packet_code', $search)
                    ->or_like('jewellery_items.barcode', $search)
                    ->or_like('vaults.name', $search)
                    ->or_like('branches.name', $search)
                    ->group_end();
            }

            return $query;
        };

        $total = $build()->count_all_results();

        $data = $build()
            ->select('gold_packets.*, jewellery_items.barcode, vaults.name AS vault_name, branches.name AS branch_name')
            ->order_by('gold_packets.id', 'DESC')
            ->limit($per_page, ($page - 1) * $per_page)
            ->get()
            ->result_array();

        return array(
            'data' => $data,
            'total' => $total,
            'per_page' => $per_page,
            'page' => $page,
            'last_page' => (int) max(1, ceil($total / $per_page)),
        );
    }
}
