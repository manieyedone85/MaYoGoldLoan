<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Admin panel: Auctions. Ports application/controllers/api/v1/Auction.php's
 * schedule()/notice()/add_bidder()/place_bid()/declare_winner()/settle() --
 * same validation, same top-bid-wins logic, same remaining-balance-to-
 * customer formula. All methods there require BRANCH_MANAGER/
 * REGIONAL_MANAGER/ADMIN with no further per-method split, so this
 * controller gates the whole thing the same way in its constructor.
 */
class Auctions extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->require_admin_role(array('BRANCH_MANAGER', 'REGIONAL_MANAGER'));

        $this->load->model('Auction_schedule_model', 'schedules');
        $this->load->model('Auction_notice_log_model', 'notice_logs');
        $this->load->model('Auction_bidder_model', 'bidders');
        $this->load->model('Auction_bid_model', 'bids');
        $this->load->model('Auction_winner_model', 'winners');
        $this->load->model('Auction_settlement_model', 'settlements');
        $this->load->model('Gold_packet_model', 'gold_packets');
        $this->load->model('Branch_model', 'branches');
    }

    /** GET /admin/auctions */
    public function index()
    {
        $this->render('auctions', array(
            'page_title' => 'Auctions',
            'schedules' => $this->schedules->with_relations(),
            'branches' => $this->branches->all(array(), 'name ASC'),
        ));
    }

    /** POST /admin/auctions/schedule */
    public function schedule()
    {
        $branch_id = $this->input->post('branch_id');
        if (! $branch_id || ! $this->branches->find($branch_id)) {
            return $this->_fail_index('A valid branch is required.');
        }

        $auction_date = trim((string) $this->input->post('auction_date'));
        if ($auction_date === '' || strtotime($auction_date) === false) {
            return $this->_fail_index('A valid auction date is required.');
        }
        if (strtotime($auction_date) <= strtotime('today')) {
            return $this->_fail_index('Auction date must be after today.');
        }

        $id = $this->schedules->insert(array(
            'branch_id' => $branch_id,
            'auction_date' => $auction_date,
            'status' => 'SCHEDULED',
            'created_by' => $this->user['id'],
        ));

        $this->audit_log('AuctionSchedule', $id, 'AUCTION_SCHEDULE', null, array('branch_id' => $branch_id, 'auction_date' => $auction_date));

        $this->session->set_flashdata('status', 'Auction scheduled.');
        redirect('admin/auctions');
    }

    /** GET /admin/auctions/(:num) */
    public function show($id)
    {
        $schedule = $this->schedules->find($id);
        if (! $schedule) {
            show_404();

            return;
        }

        $this->render('auction_show', array(
            'page_title' => 'Auction — ' . date('d-M-Y', strtotime($schedule['auction_date'])),
            'schedule' => $schedule,
            'notices' => $this->notice_logs->all(array('auction_schedule_id' => $id), 'id DESC'),
            'bidders' => $this->bidders->all(array('auction_schedule_id' => $id), 'id DESC'),
            'bids' => $this->bids->all(array('auction_schedule_id' => $id), 'id DESC'),
            'winners' => $this->winners->for_schedule($id),
        ));
    }

    /** POST /admin/auctions/(:num)/notice */
    public function notice($id)
    {
        $schedule = $this->schedules->find($id);
        if (! $schedule) {
            show_404();

            return;
        }

        $loan_id = $this->input->post('loan_id');
        if (! $loan_id || ! $this->db->where('id', $loan_id)->get('loans')->row_array()) {
            return $this->_fail_show($id, 'A valid loan id is required.');
        }

        $channel = trim((string) $this->input->post('channel'));
        if (! in_array($channel, array('SMS', 'EMAIL', 'POST'), true)) {
            return $this->_fail_show($id, 'Channel must be one of SMS, EMAIL, POST.');
        }

        $this->notice_logs->insert(array(
            'auction_schedule_id' => $id,
            'loan_id' => $loan_id,
            'channel' => $channel,
            'sent_at' => date('Y-m-d H:i:s'),
        ));

        $this->schedules->update($id, array('status' => 'NOTICE_SENT'));

        $this->session->set_flashdata('status', 'Notice logged.');
        redirect('admin/auctions/' . $id);
    }

    /** POST /admin/auctions/(:num)/bidder */
    public function add_bidder($id)
    {
        $schedule = $this->schedules->find($id);
        if (! $schedule) {
            show_404();

            return;
        }

        $name = trim((string) $this->input->post('name'));
        $mobile = trim((string) $this->input->post('mobile'));
        if ($name === '' || $mobile === '') {
            return $this->_fail_show($id, 'Name and mobile are required.');
        }

        $id_proof_number = trim((string) $this->input->post('id_proof_number'));

        $this->bidders->insert(array(
            'auction_schedule_id' => $id,
            'name' => $name,
            'mobile' => $mobile,
            'id_proof_number' => $id_proof_number !== '' ? $id_proof_number : null,
        ));

        $this->session->set_flashdata('status', 'Bidder added.');
        redirect('admin/auctions/' . $id);
    }

    /** POST /admin/auctions/(:num)/bid */
    public function place_bid($id)
    {
        $schedule = $this->schedules->find($id);
        if (! $schedule) {
            show_404();

            return;
        }

        $gold_packet_id = $this->input->post('gold_packet_id');
        if (! $gold_packet_id || ! $this->gold_packets->find($gold_packet_id)) {
            return $this->_fail_show($id, 'A valid gold packet is required.');
        }

        $bidder_id = $this->input->post('bidder_id');
        if (! $bidder_id || ! $this->bidders->find($bidder_id)) {
            return $this->_fail_show($id, 'A valid bidder is required.');
        }

        $bid_amount = $this->input->post('bid_amount');
        if (! is_numeric($bid_amount) || (float) $bid_amount < 0.01) {
            return $this->_fail_show($id, 'Bid amount must be at least 0.01.');
        }

        $this->bids->insert(array(
            'auction_schedule_id' => $id,
            'gold_packet_id' => $gold_packet_id,
            'bidder_id' => $bidder_id,
            'bid_amount' => $bid_amount,
        ));

        $this->session->set_flashdata('status', 'Bid recorded.');
        redirect('admin/auctions/' . $id);
    }

    /** POST /admin/auctions/(:num)/winner */
    public function declare_winner($id)
    {
        $schedule = $this->schedules->find($id);
        if (! $schedule) {
            show_404();

            return;
        }

        $gold_packet_id = $this->input->post('gold_packet_id');
        $gold_packet = $gold_packet_id ? $this->gold_packets->find($gold_packet_id) : null;
        if (! $gold_packet) {
            return $this->_fail_show($id, 'A valid gold packet is required.');
        }

        $top_bid = $this->bids->top_bid($id, $gold_packet_id);
        if (! $top_bid) {
            return $this->_fail_show($id, 'No bids found for this gold packet in this auction.');
        }

        $winner_id = $this->winners->insert(array(
            'gold_packet_id' => $gold_packet_id,
            'bidder_id' => $top_bid['bidder_id'],
            'winning_amount' => $top_bid['bid_amount'],
        ));

        $this->gold_packets->update($gold_packet_id, array('status' => 'AUCTIONED'));

        $this->audit_log('GoldPacket', $gold_packet_id, 'AUCTION_DECLARE_WINNER',
            array('status' => $gold_packet['status']),
            array('status' => 'AUCTIONED', 'winner_id' => $winner_id, 'bidder_id' => $top_bid['bidder_id'], 'winning_amount' => $top_bid['bid_amount'])
        );

        $this->session->set_flashdata('status', 'Winner declared: ₹' . number_format($top_bid['bid_amount'], 2) . '.');
        redirect('admin/auctions/' . $id);
    }

    /** POST /admin/auctions/(:num)/settle */
    public function settle($id)
    {
        $schedule = $this->schedules->find($id);
        if (! $schedule) {
            show_404();

            return;
        }

        $loan_id = $this->input->post('loan_id');
        if (! $loan_id || ! $this->db->where('id', $loan_id)->get('loans')->row_array()) {
            return $this->_fail_show($id, 'A valid loan id is required.');
        }

        $gold_packet_id = $this->input->post('gold_packet_id');
        if (! $gold_packet_id || ! $this->gold_packets->find($gold_packet_id)) {
            return $this->_fail_show($id, 'A valid gold packet is required.');
        }

        $outstanding_loan_amount = $this->input->post('outstanding_loan_amount');
        $auction_amount = $this->input->post('auction_amount');
        if (! is_numeric($outstanding_loan_amount) || ! is_numeric($auction_amount)) {
            return $this->_fail_show($id, 'Outstanding loan amount and auction amount are required.');
        }

        $remaining = max(0, round((float) $auction_amount - (float) $outstanding_loan_amount, 2));

        $settlement_id = $this->settlements->insert(array(
            'loan_id' => $loan_id,
            'gold_packet_id' => $gold_packet_id,
            'outstanding_loan_amount' => $outstanding_loan_amount,
            'auction_amount' => $auction_amount,
            'remaining_balance_to_customer' => $remaining,
            'settled_by' => $this->user['id'],
        ));

        $this->audit_log('Loan', $loan_id, 'AUCTION_SETTLE', null, array(
            'gold_packet_id' => $gold_packet_id, 'outstanding_loan_amount' => $outstanding_loan_amount,
            'auction_amount' => $auction_amount, 'remaining_balance_to_customer' => $remaining,
        ));

        $this->session->set_flashdata('status', 'Auction settled. Remaining balance to customer: ₹' . number_format($remaining, 2) . '.');
        redirect('admin/auctions/' . $id);
    }

    private function _fail_index($message)
    {
        $this->session->set_flashdata('error', $message);
        redirect('admin/auctions');
    }

    private function _fail_show($id, $message)
    {
        $this->session->set_flashdata('error', $message);
        redirect('admin/auctions/' . $id);
    }
}
