<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Jewellery\EvaluateJewelleryRequest;
use App\Models\GoldRate;
use App\Models\JewelleryImage;
use App\Models\JewelleryItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class JewelleryController extends Controller
{
    /**
     * POST /api/v1/jewellery/evaluate
     * Net weight = gross - stone (also enforced by a DB trigger as defense-in-depth).
     */
    public function evaluate(EvaluateJewelleryRequest $request): JsonResponse
    {
        $data = $request->validated();

        $goldRate = GoldRate::where('karat', $data['purity_karat'])
            ->where('status', 'APPROVED')
            ->latest('effective_date')
            ->firstOrFail();

        $grossWeight = (float) $data['gross_weight'];
        $stoneWeight = (float) ($data['stone_weight'] ?? 0);
        $netWeight = $grossWeight - $stoneWeight;
        $eligiblePercentage = 75.00; // pulled from loan_product config in a full implementation
        $eligibleAmount = round($netWeight * (float) $goldRate->rate_per_gram * ($eligiblePercentage / 100), 2);

        $item = JewelleryItem::create([
            'barcode' => 'JWL' . strtoupper(Str::random(10)),
            'customer_id' => $data['customer_id'],
            'category_id' => $data['category_id'],
            'hallmark_flag' => $data['hallmark_flag'] ?? false,
            'gross_weight' => $grossWeight,
            'stone_weight' => $stoneWeight,
            'net_weight' => $netWeight,
            'purity_karat' => $data['purity_karat'],
            'gold_rate_id' => $goldRate->id,
            'applied_rate' => $goldRate->rate_per_gram,
            'eligible_percentage' => $eligiblePercentage,
            'eligible_amount' => $eligibleAmount,
            'evaluated_by' => $request->user()->id,
            'status' => 'EVALUATED',
        ]);

        return response()->json(['data' => $item], 201);
    }

    /**
     * GET /api/v1/jewellery/rate/current
     */
    public function currentRate(Request $request): JsonResponse
    {
        $request->validate(['karat' => ['required', 'string']]);

        $rate = GoldRate::where('karat', $request->input('karat'))
            ->where('status', 'APPROVED')
            ->latest('effective_date')
            ->firstOrFail();

        return response()->json(['data' => $rate]);
    }

    /**
     * POST /api/v1/jewellery/rate/propose
     * Gold Rate Approval Workflow -- rate changes require Manager approval before taking effect.
     */
    public function proposeRate(Request $request): JsonResponse
    {
        $data = $request->validate([
            'rate_per_gram' => ['required', 'numeric', 'min:0'],
            'karat' => ['required', 'string', 'max:5'],
            'effective_date' => ['required', 'date'],
        ]);

        $rate = GoldRate::create(array_merge($data, [
            'status' => 'PENDING_APPROVAL',
            'proposed_by' => $request->user()->id,
        ]));

        return response()->json(['data' => $rate], 201);
    }

    /**
     * POST /api/v1/jewellery/rate/{id}/approve
     */
    public function approveRate(Request $request, GoldRate $goldRate): JsonResponse
    {
        abort_unless($request->user()->hasAnyRole(['BRANCH_MANAGER', 'REGIONAL_MANAGER']), 403);

        $goldRate->update([
            'status' => 'APPROVED',
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
        ]);

        return response()->json(['data' => $goldRate]);
    }

    /**
     * POST /api/v1/jewellery/{id}/image
     */
    public function uploadImage(Request $request, JewelleryItem $jewelleryItem): JsonResponse
    {
        $request->validate(['image' => ['required', 'image', 'max:5120']]);

        $path = $request->file('image')->store('jewellery-images');

        $image = JewelleryImage::create([
            'jewellery_item_id' => $jewelleryItem->id,
            'file_ref' => $path,
        ]);

        return response()->json(['data' => $image], 201);
    }

    /**
     * GET /api/v1/jewellery/{id}/barcode
     */
    public function barcode(JewelleryItem $jewelleryItem): JsonResponse
    {
        return response()->json(['barcode' => $jewelleryItem->barcode]);
    }
}
