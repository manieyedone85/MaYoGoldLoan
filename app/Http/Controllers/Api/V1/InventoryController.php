<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\GoldPacket;
use App\Models\PacketTransferLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    /** POST /api/v1/inventory/packet */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'jewellery_item_id' => ['required', 'exists:jewellery_items,id'],
            'vault_id' => ['required', 'exists:vaults,id'],
        ]);

        $packet = GoldPacket::create(array_merge($data, [
            'packet_code' => 'PKT' . str_pad((string) (GoldPacket::max('id') + 1), 8, '0', STR_PAD_LEFT),
            'status' => 'IN_VAULT',
        ]));

        return response()->json(['data' => $packet], 201);
    }

    /**
     * POST /api/v1/inventory/packet/{id}/transfer
     * State machine: IN_VAULT -> PLEDGED -> IN_VAULT (on closure) -> AUCTION_ELIGIBLE -> AUCTIONED/RELEASED.
     */
    public function transfer(Request $request, GoldPacket $goldPacket): JsonResponse
    {
        $data = $request->validate(['to_vault_id' => ['required', 'exists:vaults,id']]);

        PacketTransferLog::create([
            'gold_packet_id' => $goldPacket->id,
            'from_vault_id' => $goldPacket->vault_id,
            'to_vault_id' => $data['to_vault_id'],
            'transferred_by' => $request->user()->id,
        ]);

        $goldPacket->update(['vault_id' => $data['to_vault_id']]);

        return response()->json(['data' => $goldPacket]);
    }

    /** GET /api/v1/inventory/vault/{branchId}/status */
    public function vaultStatus(int $branchId): JsonResponse
    {
        $counts = GoldPacket::whereHas('vault', function ($q) use ($branchId) {
            $q->where('branch_id', $branchId);
        })
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->get();

        return response()->json(['data' => $counts]);
    }
}
