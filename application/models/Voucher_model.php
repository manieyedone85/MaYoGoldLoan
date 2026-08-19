<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Voucher_model extends MY_Model
{
    protected $table = 'vouchers';

    public function next_voucher_number()
    {
        $max_id = (int) $this->db->select_max('id')->get($this->table)->row('id');

        return 'VCH' . date('Ymd') . str_pad((string) ($max_id + 1), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Vouchers joined with branch name, newest first, paginated with
     * optional search across voucher number / type / branch name -- for the
     * admin Accounting list.
     */
    public function with_relations($search = '', $per_page = 15, $page = 1)
    {
        $build = function () use ($search) {
            $query = $this->db->from('vouchers')
                ->join('branches', 'branches.id = vouchers.branch_id', 'left');

            if ($search !== '') {
                $query->group_start()
                    ->like('vouchers.voucher_number', $search)
                    ->or_like('vouchers.type', $search)
                    ->or_like('branches.name', $search)
                    ->group_end();
            }

            return $query;
        };

        $total = $build()->count_all_results();

        $data = $build()
            ->select('vouchers.*, branches.name AS branch_name')
            ->order_by('vouchers.id', 'DESC')
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
