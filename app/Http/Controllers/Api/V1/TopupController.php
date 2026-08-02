<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Loan;
use App\Models\LoanTopup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TopupController extends Controller
{
    /**
     * GET /api/v1/loan/{id}/topup/eligibility
     * Re-values current jewellery against latest approved gold rate.
     */
    public function eligibility(Loan $loan): JsonResponse
    {
        $currentValue = $loan->jewelleryItems->sum(function ($item) {
            $latestRate = \App\Models\GoldRate::where('karat', $item->purity_karat)
                ->where('status', 'APPROVED')->latest('effective_date')->first();

            return $latestRate ? $item->net_weight * $latestRate->rate_per_gram * ($item->eligible_percentage / 100) : 0;
        });

        $eligibleTopup = max(0, round($currentValue - $loan->sanctioned_amount, 2));

        return response()->json(['eligible_topup_amount' => $eligibleTopup]);
    }

    /**
     * POST /api/v1/loan/{id}/topup/approve
     */
    public function approve(Request $request, Loan $loan): JsonResponse
    {
        $data = $request->validate(['approved_amount' => ['required', 'numeric', 'min:0']]);

        $topup = LoanTopup::create([
            'loan_id' => $loan->id,
            'eligible_topup_amount' => $data['approved_amount'],
            'approved_amount' => $data['approved_amount'],
            'status' => 'APPROVED',
            'approved_by' => $request->user()->id,
        ]);

        return response()->json(['data' => $topup], 201);
    }

    /**
     * POST /api/v1/loan/{id}/topup/disburse
     */
    public function disburse(Request $request, Loan $loan): JsonResponse
    {
        $topup = $loan->topups()->where('status', 'APPROVED')->latest()->firstOrFail();
        $topup->update(['status' => 'DISBURSED']);
        $loan->update(['sanctioned_amount' => $loan->sanctioned_amount + $topup->approved_amount]);

        return response()->json(['data' => $topup]);
    }
}
