<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\GoldRelease;
use App\Models\Loan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GoldReleaseController extends Controller
{
    /**
     * POST /api/v1/loan/{id}/gold-release/verify-id
     */
    public function verifyId(Request $request, Loan $loan): JsonResponse
    {
        $data = $request->validate([
            'jewellery_item_id' => ['required', 'exists:jewellery_items,id'],
            'released_to' => ['required', 'string'],
        ]);

        $release = GoldRelease::firstOrCreate(
            ['loan_id' => $loan->id, 'jewellery_item_id' => $data['jewellery_item_id']],
            ['released_by' => $request->user()->id, 'released_to' => $data['released_to'], 'status' => 'PENDING']
        );

        $release->update(['id_proof_verified' => true]);

        return response()->json(['data' => $release]);
    }

    /**
     * POST /api/v1/loan/{id}/gold-release/capture-signature
     */
    public function captureSignature(Request $request, GoldRelease $goldRelease): JsonResponse
    {
        $goldRelease->update(['signature_captured' => true]);

        return response()->json(['data' => $goldRelease]);
    }

    /**
     * POST /api/v1/loan/{id}/gold-release/complete
     * Blocked unless all three checklist gates are true, and the jewellery item
     * is still IN_VAULT (not already flagged for auction).
     */
    public function complete(Request $request, GoldRelease $goldRelease): JsonResponse
    {
        abort_unless($goldRelease->isReadyForRelease(), 422, 'ID proof, signature, and photo must all be captured first.');

        $goldRelease->update(['status' => 'RELEASED', 'released_at' => now()]);
        if ($goldRelease->jewelleryItem) {
            $goldRelease->jewelleryItem->update(['status' => 'RELEASED']);
        }

        return response()->json(['data' => $goldRelease]);
    }
}
