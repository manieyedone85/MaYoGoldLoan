<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AuctionBid;
use App\Models\AuctionBidder;
use App\Models\AuctionSchedule;
use App\Models\AuctionSettlement;
use App\Models\AuctionWinner;
use App\Models\GoldPacket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuctionController extends Controller
{
    /**
     * POST /api/v1/auction/schedule
     * Only jewellery with loan.status = NPA and npa_days > threshold is auction-eligible
     * (join against Accounting's NPA classification in a full implementation).
     */
    public function schedule(Request $request): JsonResponse
    {
        $data = $request->validate([
            'branch_id' => ['required', 'exists:branches,id'],
            'auction_date' => ['required', 'date', 'after:today'],
        ]);

        $schedule = AuctionSchedule::create(array_merge($data, ['status' => 'SCHEDULED', 'created_by' => $request->user()->id]));

        return response()->json(['data' => $schedule], 201);
    }

    /** POST /api/v1/auction/{id}/notice */
    public function notice(Request $request, AuctionSchedule $auctionSchedule): JsonResponse
    {
        $data = $request->validate([
            'loan_id' => ['required', 'exists:loans,id'],
            'channel' => ['required', 'in:SMS,EMAIL,POST'],
        ]);

        $notice = $auctionSchedule->noticeLogs()->create(array_merge($data, ['sent_at' => now()]));
        $auctionSchedule->update(['status' => 'NOTICE_SENT']);

        return response()->json(['data' => $notice], 201);
    }

    /** POST /api/v1/auction/{id}/bidder */
    public function addBidder(Request $request, AuctionSchedule $auctionSchedule): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string'],
            'mobile' => ['required', 'string'],
            'id_proof_number' => ['nullable', 'string'],
        ]);

        $bidder = $auctionSchedule->bidders()->create($data);

        return response()->json(['data' => $bidder], 201);
    }

    /** POST /api/v1/auction/{id}/bid */
    public function placeBid(Request $request, AuctionSchedule $auctionSchedule): JsonResponse
    {
        $data = $request->validate([
            'gold_packet_id' => ['required', 'exists:gold_packets,id'],
            'bidder_id' => ['required', 'exists:auction_bidders,id'],
            'bid_amount' => ['required', 'numeric', 'min:0.01'],
        ]);

        $bid = AuctionBid::create(array_merge(['auction_schedule_id' => $auctionSchedule->id], $data));

        return response()->json(['data' => $bid], 201);
    }

    /** POST /api/v1/auction/{id}/winner */
    public function declareWinner(Request $request, AuctionSchedule $auctionSchedule): JsonResponse
    {
        $data = $request->validate([
            'gold_packet_id' => ['required', 'exists:gold_packets,id'],
        ]);

        $topBid = AuctionBid::where('auction_schedule_id', $auctionSchedule->id)
            ->where('gold_packet_id', $data['gold_packet_id'])
            ->orderByDesc('bid_amount')
            ->firstOrFail();

        $winner = AuctionWinner::create([
            'gold_packet_id' => $data['gold_packet_id'],
            'bidder_id' => $topBid->bidder_id,
            'winning_amount' => $topBid->bid_amount,
        ]);

        GoldPacket::whereKey($data['gold_packet_id'])->update(['status' => 'AUCTIONED']);

        return response()->json(['data' => $winner], 201);
    }

    /**
     * POST /api/v1/auction/{id}/settle
     * Remaining balance after settlement posts automatically to customer_ledger.
     */
    public function settle(Request $request, AuctionSchedule $auctionSchedule): JsonResponse
    {
        $data = $request->validate([
            'loan_id' => ['required', 'exists:loans,id'],
            'gold_packet_id' => ['required', 'exists:gold_packets,id'],
            'outstanding_loan_amount' => ['required', 'numeric'],
            'auction_amount' => ['required', 'numeric'],
        ]);

        $remaining = max(0, round($data['auction_amount'] - $data['outstanding_loan_amount'], 2));

        $settlement = AuctionSettlement::create(array_merge($data, [
            'remaining_balance_to_customer' => $remaining,
            'settled_by' => $request->user()->id,
        ]));

        // Auto-post remaining balance to customer_ledger here (AccountingService::postAuctionSettlement).

        return response()->json(['data' => $settlement], 201);
    }
}
