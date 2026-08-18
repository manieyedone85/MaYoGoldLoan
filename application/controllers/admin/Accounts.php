<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Admin panel: Accounting (vouchers & customer ledger). Ports
 * application/controllers/api/v1/Accounting.php's store_voucher()/
 * customer_ledger() -- same balanced-debit-equals-credit validation, same
 * voucher-number generation. GL account master CRUD lives in
 * admin/Masters.php (pure config).
 *
 * Named `Accounts` (not `Accounting`) purely to avoid declaring a second
 * PHP class literally named `Accounting` alongside
 * `api/v1/Accounting.php`'s `class Accounting extends Api_Controller` --
 * same reasoning behind every other admin controller in this codebase being
 * pluralized against its singular api/v1 counterpart (Loans vs Loan,
 * Renewals vs Renewal, etc.). The URL stays `admin/accounting` either way
 * (see routes.php); this is only the controller class/file name.
 */
class Accounts extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->require_admin_role(array('FINANCE'));

        $this->load->model('Voucher_model', 'vouchers');
        $this->load->model('Voucher_detail_model', 'voucher_details');
        $this->load->model('Customer_ledger_model', 'customer_ledgers');
        $this->load->model('Gl_account_model', 'gl_accounts');
        $this->load->model('Branch_model', 'branches');
        $this->load->model('Customer_model', 'customers');
    }

    /** GET /admin/accounting */
    public function index()
    {
        $customer_mobile = trim((string) $this->input->get('customer_mobile'));
        $ledger_customer = null;
        $ledger = array();

        if ($customer_mobile !== '') {
            $ledger_customer = $this->customers->find_by_mobile($customer_mobile);
            if ($ledger_customer) {
                $ledger = $this->customer_ledgers->for_customer($ledger_customer['id']);
            }
        }

        $vouchers = $this->vouchers->with_relations(50);
        foreach ($vouchers as &$v) {
            $v['details'] = $this->voucher_details->for_voucher($v['id']);
        }
        unset($v);

        $this->render('accounting', array(
            'page_title' => 'Accounting',
            'vouchers' => $vouchers,
            'gl_accounts' => $this->gl_accounts->all(array(), 'code ASC'),
            'branches' => $this->branches->all(array(), 'name ASC'),
            'customer_mobile' => $customer_mobile,
            'ledger_customer' => $ledger_customer,
            'ledger' => $ledger,
        ));
    }

    /** POST /admin/accounting/voucher */
    public function store_voucher()
    {
        $branch_id = $this->input->post('branch_id');
        if (! $branch_id || ! $this->branches->find($branch_id)) {
            return $this->_fail('A valid branch is required.');
        }

        $type = trim((string) $this->input->post('type'));
        if (! in_array($type, array('RECEIPT', 'PAYMENT', 'JOURNAL', 'CONTRA'), true)) {
            return $this->_fail('Type must be one of RECEIPT, PAYMENT, JOURNAL, CONTRA.');
        }

        $voucher_date = trim((string) $this->input->post('voucher_date'));
        if ($voucher_date === '' || strtotime($voucher_date) === false) {
            return $this->_fail('A valid voucher date is required.');
        }

        $gl_account_ids = (array) $this->input->post('gl_account_id');
        $debits = (array) $this->input->post('debit');
        $credits = (array) $this->input->post('credit');

        $lines = array();
        $total_debit = 0;
        $total_credit = 0;

        foreach ($gl_account_ids as $i => $gl_account_id) {
            if ($gl_account_id === '' || $gl_account_id === null) {
                continue;
            }
            if (! $this->gl_accounts->find($gl_account_id)) {
                return $this->_fail('Each line requires a valid GL account.');
            }

            $debit = isset($debits[$i]) && $debits[$i] !== '' ? (float) $debits[$i] : 0;
            $credit = isset($credits[$i]) && $credits[$i] !== '' ? (float) $credits[$i] : 0;
            if ($debit < 0 || $credit < 0) {
                return $this->_fail('Debit and credit must not be negative.');
            }

            $lines[] = array('gl_account_id' => $gl_account_id, 'debit' => $debit, 'credit' => $credit);
            $total_debit += $debit;
            $total_credit += $credit;
        }

        if (count($lines) < 2) {
            return $this->_fail('At least 2 lines (debit + credit) are required.');
        }
        if (round($total_debit, 2) !== round($total_credit, 2)) {
            return $this->_fail('Voucher must balance: total debit (' . $total_debit . ') must equal total credit (' . $total_credit . ').');
        }

        $this->db->trans_start();

        $voucher_id = $this->vouchers->insert(array(
            'voucher_number' => $this->vouchers->next_voucher_number(),
            'branch_id' => $branch_id,
            'type' => $type,
            'voucher_date' => $voucher_date,
            'created_by' => $this->user['id'],
        ));

        foreach ($lines as $line) {
            $this->voucher_details->insert(array(
                'voucher_id' => $voucher_id,
                'gl_account_id' => $line['gl_account_id'],
                'debit' => $line['debit'],
                'credit' => $line['credit'],
            ));
        }

        $this->audit_log('Voucher', $voucher_id, 'VOUCHER_CREATE', null, array(
            'branch_id' => $branch_id, 'type' => $type, 'voucher_date' => $voucher_date,
            'total_debit' => $total_debit, 'total_credit' => $total_credit,
        ));

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            return $this->_fail('Failed to save voucher.');
        }

        $this->session->set_flashdata('status', 'Voucher recorded.');
        redirect('admin/accounting');
    }

    private function _fail($message)
    {
        $this->session->set_flashdata('error', $message);
        redirect('admin/accounting');
    }
}
