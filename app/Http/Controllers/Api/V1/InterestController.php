<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\InterestCollection;
use App\Models\Loan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class InterestController extends Controller
{
    /**
     * GET /api/v1/loan/{id}/interest/due
     */
    public function due(Loan $loan): JsonResponse
    {
        $monthsElapsed = now()->diffInMonths($loan->loan_date);
        $monthlyInterest = round(($loan->sanctioned_amount * $loan->interest_rate_pct / 100) / 12, 2);
        $totalPaid = (float) $loan->interestCollections()->sum('amount');

        return response()->json([
            'interest_due' => max(0, round(($monthlyInterest * $monthsElapsed) - $totalPaid, 2)),
        ]);
    }

    /**
     * POST /api/v1/loan/{id}/interest/collect
     */
    public function collect(Request $request, Loan $loan): JsonResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'mode' => ['required', 'in:CASH,ONLINE'],
        ]);

        $collection = InterestCollection::create([
            'loan_id' => $loan->id,
            'amount' => $data['amount'],
            'mode' => $data['mode'],
            'receipt_number' => 'RCPT' . strtoupper(Str::random(10)),
            'collected_by' => $request->user()->id,
        ]);

        return response()->json(['data' => $collection], 201);
    }
}
