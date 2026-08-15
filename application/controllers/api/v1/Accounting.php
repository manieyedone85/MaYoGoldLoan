<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Mirrors app/Http/Controllers/Api/V1/AccountingController.php.
 * All methods require auth + role:FINANCE,ADMIN
 * (see routes_modules/api_auction_inventory_accounting.php).
 */
class Accounting extends Api_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->require_auth();
        $this->require_role(array('FINANCE', 'ADMIN'));

        $this->load->model('Voucher_model', 'vouchers');
        $this->load->model('Voucher_detail_model', 'voucher_details');
        $this->load->model('Customer_ledger_model', 'customer_ledgers');
    }

    /** POST /api/v1/accounting/voucher */
    public function store_voucher()
    {
        $data = $this->json_input();
        
        if (empty($data['branch_id']) || ! $this->db->where('id', $data['branch_id'])->get('branches')->row_array()) {
            return json_error('branch_id is required and must exist.');
        }
        if (empty($data['type']) || ! in_array($data['type'], array('RECEIPT', 'PAYMENT', 'JOURNAL', 'CONTRA'), true)) {
            return json_error('type must be one of RECEIPT, PAYMENT, JOURNAL, CONTRA.');
        }
        if (empty($data['voucher_date']) || strtotime($data['voucher_date']) === false) {
            return json_error('voucher_date is required and must be a valid date.');
        }
        if (empty($data['lines']) || ! is_array($data['lines']) || count($data['lines']) < 2) {
            return json_error('lines is required and must contain at least 2 entries (debit + credit).');
        }

        $total_debit = 0;
        $total_credit = 0;

        foreach ($data['lines'] as $line) {
            if (empty($line['gl_account_id']) || ! $this->db->where('id', $line['gl_account_id'])->get('gl_accounts')->row_array()) {
                return json_error('Each line requires a valid gl_account_id.');
            }
            $debit = isset($line['debit']) ? (float) $line['debit'] : 0;
            $credit = isset($line['credit']) ? (float) $line['credit'] : 0;
            if ($debit < 0 || $credit < 0) {
                return json_error('debit and credit must not be negative.');
            }
            $total_debit += $debit;
            $total_credit += $credit;
        }

        if (round($total_debit, 2) !== round($total_credit, 2)) {
            return json_error('Voucher must balance: total debit must equal total credit.');
        }

        $this->db->trans_start();

        $voucher_id = $this->vouchers->insert(array(
            'voucher_number' => $this->vouchers->next_voucher_number(),
            'branch_id' => $data['branch_id'],
            'type' => $data['type'],
            'voucher_date' => $data['voucher_date'],
            'created_by' => $this->user['id'],
        ));

        foreach ($data['lines'] as $line) {
            $this->voucher_details->insert(array(
                'voucher_id' => $voucher_id,
                'gl_account_id' => $line['gl_account_id'],
                'debit' => isset($line['debit']) ? $line['debit'] : 0,
                'credit' => isset($line['credit']) ? $line['credit'] : 0,
            ));
        }

        $this->audit_log('Voucher', $voucher_id, 'VOUCHER_CREATE', null, array(
            'branch_id' => $data['branch_id'],
            'type' => $data['type'],
            'voucher_date' => $data['voucher_date'],
            'total_debit' => $total_debit,
            'total_credit' => $total_credit,
        ));

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            return json_error('Failed to save voucher.', 500);
        }

        $voucher = $this->vouchers->find($voucher_id);
        $voucher['details'] = $this->voucher_details->for_voucher($voucher_id);

        return json_response(array('data' => $voucher), 201);
    }

    /** GET /api/v1/accounting/ledger/{customerId} */
    public function customer_ledger($customer_id)
    {
        return json_response(array('data' => $this->customer_ledgers->for_customer($customer_id)));
    }
}
