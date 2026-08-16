<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Loan_model extends MY_Model
{
    protected $table = 'loans';

    /**
     * Deterministic loan account number derived from the row's own
     * AUTO_INCREMENT id -- replaces a former SELECT MAX(id)+1 approach that
     * was non-atomic (two concurrent inserts could read the same MAX(id)
     * before either committed). MySQL already allocates `id` atomically, so
     * deriving the number from it needs no locking or retry logic.
     *
     * Not called at loan creation: BRD §9 "Unique Loan ID created after
     * disbursement" (docs/BRD_COVERAGE_AUDIT.md) means the number is only
     * assigned once, in Disbursement::disburse().
     */
    public function loan_account_number_for_id($id)
    {
        return 'LGH001' . str_pad((string) $id, 9, '0', STR_PAD_LEFT);
    }

    /**
     * Loans joined with customer name/mobile and branch name — used by the
     * admin dashboard/list pages instead of N+1 lookups per row.
     */
    public function with_relations($where = array(), $order_by = 'loans.id DESC', $limit = null, $offset = 0)
    {
        $query = $this->db->select('loans.*, customers.name AS customer_name, customers.mobile AS customer_mobile, branches.name AS branch_name, loan_products.name AS product_name')
            ->from('loans')
            ->join('customers', 'customers.id = loans.customer_id', 'left')
            ->join('branches', 'branches.id = loans.branch_id', 'left')
            ->join('loan_products', 'loan_products.id = loans.loan_product_id', 'left');

        if (! empty($where)) {
            $query->where($where);
        }
        if ($order_by) {
            $query->order_by($order_by);
        }
        if ($limit) {
            $query->limit($limit, $offset);
        }

        return $query->get()->result_array();
    }

    public function recent_with_relations($limit = 8)
    {
        return $this->with_relations(array(), 'loans.id DESC', $limit);
    }

    public function find_with_relations($id)
    {
        $rows = $this->with_relations(array('loans.id' => $id));

        return $rows ? $rows[0] : null;
    }

    /**
     * Added for the admin Loans list page: paginated loans joined with
     * customer/branch/product names, with optional loan_account_number
     * search plus status/branch filters — mirrors Admin\Loans\Index::render()
     * in Laravel.
     */
    public function admin_search($search = '', $status = '', $branch_id = '', $per_page = 12, $page = 1)
    {
        $build = function () use ($search, $status, $branch_id) {
            $query = $this->db->from('loans')
                ->join('customers', 'customers.id = loans.customer_id', 'left')
                ->join('branches', 'branches.id = loans.branch_id', 'left')
                ->join('loan_products', 'loan_products.id = loans.loan_product_id', 'left');

            if ($search !== '') {
                $query->like('loans.loan_account_number', $search);
            }
            if ($status !== '') {
                $query->where('loans.status', $status);
            }
            if ($branch_id !== '') {
                $query->where('loans.branch_id', $branch_id);
            }

            return $query;
        };

        $total = $build()->count_all_results();

        $data = $build()
            ->select('loans.*, customers.name AS customer_name, branches.name AS branch_name, loan_products.name AS product_name')
            ->order_by('loans.id', 'DESC')
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
