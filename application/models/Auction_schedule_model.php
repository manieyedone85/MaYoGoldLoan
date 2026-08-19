<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auction_schedule_model extends MY_Model
{
    protected $table = 'auction_schedules';

    /**
     * Schedules joined with branch name, newest first, paginated -- for the
     * admin Auctions list. Optional search matches branch name or status.
     */
    public function with_relations($search = '', $per_page = 15, $page = 1)
    {
        $build = function () use ($search) {
            $query = $this->db->from('auction_schedules')
                ->join('branches', 'branches.id = auction_schedules.branch_id', 'left');

            if ($search !== '') {
                $query->group_start()
                    ->like('branches.name', $search)
                    ->or_like('auction_schedules.status', $search)
                    ->group_end();
            }

            return $query;
        };

        $total = $build()->count_all_results();

        $data = $build()
            ->select('auction_schedules.*, branches.name AS branch_name')
            ->order_by('auction_schedules.id', 'DESC')
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
