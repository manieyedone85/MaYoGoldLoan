<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Customer_model extends MY_Model
{
    protected $table = 'customers';

    public function all($where = array(), $order_by = null)
    {
        // Respect soft-deletes (deleted_at) like the Laravel Customer model.
        $query = $this->db->from($this->table)->where('deleted_at IS NULL', null, false);
        if (! empty($where)) {
            $query->where($where);
        }
        if ($order_by) {
            $query->order_by($order_by);
        }

        return $query->get()->result_array();
    }

    public function find($id)
    {
        return $this->first(array('id' => $id, 'deleted_at' => null));
    }

    public function find_by_mobile($mobile)
    {
        return $this->first(array('mobile' => $mobile, 'deleted_at' => null));
    }

    public function next_customer_code()
    {
        $max_id = (int) $this->db->select_max('id')->get($this->table)->row('id');

        return 'CUST' . str_pad((string) ($max_id + 1), 8, '0', STR_PAD_LEFT);
    }

    public function with_branch($id)
    {
        $customer = $this->find($id);
        if (! $customer) {
            return null;
        }

        $this->load->model('Branch_model', 'branches');
        $customer['branch'] = $this->branches->find($customer['branch_id']);

        return $customer;
    }

    /**
     * Added for the admin Customers page: paginated customer list joined
     * with branch name, with optional name/customer_code/mobile search and
     * KYC status filter — mirrors Admin\Customers\Index::render() in Laravel.
     */
    public function admin_list($search = '', $kyc_status = '', $per_page = 10, $page = 1)
    {
        $build = function () use ($search, $kyc_status) {
            $query = $this->db->from('customers')
                ->join('branches', 'branches.id = customers.branch_id', 'left')
                ->where('customers.deleted_at IS NULL', null, false);

            if ($search !== '') {
                $query->group_start()
                    ->like('customers.name', $search)
                    ->or_like('customers.customer_code', $search)
                    ->or_like('customers.mobile', $search)
                    ->group_end();
            }
            if ($kyc_status !== '') {
                $query->where('customers.kyc_status', $kyc_status);
            }

            return $query;
        };

        $total = $build()->count_all_results();

        $data = $build()
            ->select('customers.*, branches.name AS branch_name')
            ->order_by('customers.id', 'DESC')
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
