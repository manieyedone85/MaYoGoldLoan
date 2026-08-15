<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Mirrors app/Http/Controllers/Api/V1/AuctionController.php.
 * All methods require auth + role:BRANCH_MANAGER,REGIONAL_MANAGER,ADMIN
 * (see routes_modules/api_auction_inventory_accounting.php).
 */
class Auction extends Api_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->require_auth();
        $this->require_role(array('BRANCH_MANAGER', 'REGIONAL_MANAGER', 'ADMIN'));

        $this->load->model('Auction_schedule_model', 'schedules');
        $this->load->model('Auction_notice_log_model', 'notice_logs');
        $this->load->model('Auction_bidder_model', 'bidders');
        $this->load->model('Auction_bid_model', 'bids');
        $this->load->model('Auction_winner_model', 'winners');
        $this->load->model('Auction_settlement_model', 'settlements');
        $this->load->model('Gold_packet_model', 'gold_packets');
    }

    /**
     * POST /api/v1/auction/schedule
     * Only jewellery with loan.status = NPA and npa_days > threshold is
     * auction-eligible (join against Accounting's NPA classification in a
     * full implementation) — same note as the Laravel source, not enforced here.
     */
    public function schedule()
    {
        $data = $this->json_input();

        if (empty($data['branch_id']) || ! $this->db->where('id', $data['branch_id'])->get('branches')->row_array()) {
            return json_error('branch_id is required and must exist.');
        }
        if (empty($data['auction_date']) || strtotime($data['auction_date']) === false) {
            return json_error('auction_date is required and must be a valid date.');
        }
        if (strtotime($data['auction_date']) <= strtotime('today')) {
            return json_error('auction_date must be after today.');
        }

        $id = $this->schedules->insert(array(
            'branch_id' => $data['branch_id'],
            'auction_date' => $data['auction_date'],
            'status' => 'SCHEDULED',
            'created_by' => $this->user['id'],
        ));

        $this->audit_log('AuctionSchedule', $id, 'AUCTION_SCHEDULE', null, array(
            'branch_id' => $data['branch_id'],
            'auction_date' => $data['auction_date'],
            'status' => 'SCHEDULED',
        ));

        return json_response(array('data' => $this->schedules->find($id)), 201);
    }

    /** POST /api/v1/auction/{id}/notice */
    public function notice($auction_schedule_id)
    {
        $schedule = $this->schedules->find($auction_schedule_id);
        if (! $schedule) {
            return json_error('Auction schedule not found.', 404);
        }

        $data = $this->json_input();

        if (empty($data['loan_id']) || ! $this->db->where('id', $data['loan_id'])->get('loans')->row_array()) {
            return json_error('loan_id is required and must exist.');
        }
        if (empty($data['channel']) || ! in_array($data['channel'], array('SMS', 'EMAIL', 'POST'), true)) {
            return json_error('channel must be one of SMS, EMAIL, POST.');
        }

        $notice_id = $this->notice_logs->insert(array(
            'auction_schedule_id' => $auction_schedule_id,
            'loan_id' => $data['loan_id'],
            'channel' => $data['channel'],
            'sent_at' => date('Y-m-d H:i:s'),
        ));

        $this->schedules->update($auction_schedule_id, array('status' => 'NOTICE_SENT'));

        return json_response(array('data' => $this->notice_logs->find($notice_id)), 201);
    }

    /** POST /api/v1/auction/{id}/bidder */
    public function add_bidder($auction_schedule_id)
    {
        $schedule = $this->schedules->find($auction_schedule_id);
        if (! $schedule) {
            return json_error('Auction schedule not found.', 404);
        }

        $data = $this->json_input();

        if (empty($data['name']) || empty($data['mobile'])) {
            return json_error('name and mobile are required.');
        }

        $bidder_id = $this->bidders->insert(array(
            'auction_schedule_id' => $auction_schedule_id,
            'name' => $data['name'],
            'mobile' => $data['mobile'],
            'id_proof_number' => $data['id_proof_number'] ?? null,
        ));

        return json_response(array('data' => $this->bidders->find($bidder_id)), 201);
    }

    /** POST /api/v1/auction/{id}/bid */
    public function place_bid($auction_schedule_id)
    {
        $schedule = $this->schedules->find($auction_schedule_id);
        if (! $schedule) {
            return json_error('Auction schedule not found.', 404);
        }

        $data = $this->json_input();

        if (empty($data['gold_packet_id']) || ! $this->gold_packets->find($data['gold_packet_id'])) {
            return json_error('gold_packet_id is required and must exist.');
        }
        if (empty($data['bidder_id']) || ! $this->bidders->find($data['bidder_id'])) {
            return json_error('bidder_id is required and must exist.');
        }
        if (! isset($data['bid_amount']) || ! is_numeric($data['bid_amount']) || $data['bid_amount'] < 0.01) {
            return json_error('bid_amount is required and must be numeric >= 0.01.');
        }

        $bid_id = $this->bids->insert(array(
            'auction_schedule_id' => $auction_schedule_id,
            'gold_packet_id' => $data['gold_packet_id'],
            'bidder_id' => $data['bidder_id'],
            'bid_amount' => $data['bid_amount'],
        ));

        return json_response(array('data' => $this->bids->find($bid_id)), 201);
    }

    /** POST /api/v1/auction/{id}/winner */
    public function declare_winner($auction_schedule_id)
    {
        $schedule = $this->schedules->find($auction_schedule_id);
        if (! $schedule) {
            return json_error('Auction schedule not found.', 404);
        }

        $data = $this->json_input();

        $gold_packet = $this->gold_packets->find($data['gold_packet_id']);
        if (! $gold_packet) {
            return json_error('gold_packet_id is required and must exist.');
        }

        $top_bid = $this->bids->top_bid($auction_schedule_id, $data['gold_packet_id']);
        if (! $top_bid) {
            return json_error('No bids found for this gold packet in this auction.', 404);
        }

        $winner_id = $this->winners->insert(array(
            'gold_packet_id' => $data['gold_packet_id'],
            'bidder_id' => $top_bid['bidder_id'],
            'winning_amount' => $top_bid['bid_amount'],
        ));

        $this->gold_packets->update($data['gold_packet_id'], array('status' => 'AUCTIONED'));

        $this->audit_log(
            'GoldPacket',
            $data['gold_packet_id'],
            'AUCTION_DECLARE_WINNER',
            array('status' => $gold_packet['status']),
            array('status' => 'AUCTIONED', 'winner_id' => $winner_id, 'bidder_id' => $top_bid['bidder_id'], 'winning_amount' => $top_bid['bid_amount'])
        );

        return json_response(array('data' => $this->winners->find($winner_id)), 201);
    }

    /**
     * POST /api/v1/auction/{id}/settle
     * Remaining balance after settlement is meant to auto-post to
     * customer_ledger (AccountingService::postAuctionSettlement — not yet
     * implemented in the Laravel source either, so left as a stub here).
     */
    public function settle($auction_schedule_id)
    {
        $schedule = $this->schedules->find($auction_schedule_id);
        if (! $schedule) {
            return json_error('Auction schedule not found.', 404);
        }

        $data = $this->json_input();

        if (empty($data['loan_id']) || ! $this->db->where('id', $data['loan_id'])->get('loans')->row_array()) {
            return json_error('loan_id is required and must exist.');
        }
        if (empty($data['gold_packet_id']) || ! $this->gold_packets->find($data['gold_packet_id'])) {
            return json_error('gold_packet_id is required and must exist.');
        }
        if (! isset($data['outstanding_loan_amount']) || ! is_numeric($data['outstanding_loan_amount'])) {
            return json_error('outstanding_loan_amount is required and must be numeric.');
        }
        if (! isset($data['auction_amount']) || ! is_numeric($data['auction_amount'])) {
            return json_error('auction_amount is required and must be numeric.');
        }

        $remaining = max(0, round($data['auction_amount'] - $data['outstanding_loan_amount'], 2));

        $settlement_id = $this->settlements->insert(array(
            'loan_id' => $data['loan_id'],
            'gold_packet_id' => $data['gold_packet_id'],
            'outstanding_loan_amount' => $data['outstanding_loan_amount'],
            'auction_amount' => $data['auction_amount'],
            'remaining_balance_to_customer' => $remaining,
            'settled_by' => $this->user['id'],
        ));

        // Auto-post remaining balance to customer_ledger here (AccountingService::postAuctionSettlement).

        $this->audit_log(
            'Loan',
            $data['loan_id'],
            'AUCTION_SETTLE',
            null,
            array(
                'gold_packet_id' => $data['gold_packet_id'],
                'outstanding_loan_amount' => $data['outstanding_loan_amount'],
                'auction_amount' => $data['auction_amount'],
                'remaining_balance_to_customer' => $remaining,
            )
        );

        return json_response(array('data' => $this->settlements->find($settlement_id)), 201);
    }
}
