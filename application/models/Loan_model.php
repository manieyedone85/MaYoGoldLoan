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

    /**
     * Loan lookup shared by the admin panel's action screens (Interest
     * Collections, Part Payments, Top-ups, Renewals, Settlements) --
     * matches by loan account number or the owning customer's mobile, same
     * "search loan by mobile number" entry point BRD §10 describes.
     */
    public function search_by_account_or_mobile($query, $limit = 20)
    {
        $query = trim((string) $query);
        if ($query === '') {
            return array();
        }

        return $this->db->select('loans.*, customers.name AS customer_name, customers.mobile AS customer_mobile, branches.name AS branch_name')
            ->from('loans')
            ->join('customers', 'customers.id = loans.customer_id', 'left')
            ->join('branches', 'branches.id = loans.branch_id', 'left')
            ->group_start()
                ->like('loans.loan_account_number', $query)
                ->or_like('customers.mobile', $query)
                ->or_like('customers.name', $query)
            ->group_end()
            ->order_by('loans.id', 'DESC')
            ->limit($limit)
            ->get()
            ->result_array();
    }

    /**
     * Added for the admin Loan Approvals queue: paginated pending-approval
     * loans for a given workflow stage, joined with customer/branch names,
     * with optional customer name/mobile/loan_account_number search --
     * replaces the former unbounded raw join query in
     * Loan_approvals::index().
     */
    public function pending_approvals($stage, $search = '', $per_page = 15, $page = 1)
    {
        $build = function () use ($stage, $search) {
            $query = $this->db->from('loans')
                ->join('loan_approval_workflows', 'loan_approval_workflows.loan_id = loans.id')
                ->join('customers', 'customers.id = loans.customer_id', 'left')
                ->join('branches', 'branches.id = loans.branch_id', 'left')
                ->where('loan_approval_workflows.current_stage', $stage)
                ->where('loan_approval_workflows.status', 'PENDING');

            if ($search !== '') {
                $query->group_start()
                    ->like('customers.name', $search)
                    ->or_like('customers.mobile', $search)
                    ->or_like('loans.loan_account_number', $search)
                    ->group_end();
            }

            return $query;
        };

        $total = $build()->count_all_results();

        $data = $build()
            ->select('loans.*, customers.name AS customer_name, customers.mobile AS customer_mobile, branches.name AS branch_name')
            ->order_by('loans.created_at', 'ASC')
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
