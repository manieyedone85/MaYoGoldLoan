<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Loan_part_payment_model extends MY_Model
{
    protected $table = 'loan_part_payments';

    /**
     * Part-payment history joined with loan/customer, newest first,
     * paginated with optional search across loan account number / customer
     * name -- for the admin Part Payments list.
     */
    public function with_relations($search = '', $per_page = 15, $page = 1)
    {
        $build = function () use ($search) {
            $query = $this->db->from('loan_part_payments')
                ->join('loans', 'loans.id = loan_part_payments.loan_id', 'left')
                ->join('customers', 'customers.id = loans.customer_id', 'left');

            if ($search !== '') {
                $query->group_start()
                    ->like('loans.loan_account_number', $search)
                    ->or_like('customers.name', $search)
                    ->group_end();
            }

            return $query;
        };

        $total = $build()->count_all_results();

        $data = $build()
            ->select('loan_part_payments.*, loans.loan_account_number, customers.name AS customer_name')
            ->order_by('loan_part_payments.id', 'DESC')
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
