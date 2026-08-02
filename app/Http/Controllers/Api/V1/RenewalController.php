<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Loan;
use App\Models\LoanRenewal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RenewalController extends Controller
{
    /**
     * GET /api/v1/loan/{id}/renewal-eligibility
     */
    public function eligibility(Loan $loan): JsonResponse
    {
        return response()->json([
            'eligible' => in_array($loan->status, ['ACTIVE', 'PART_PAID'], true),
            'interest_due' => round(($loan->sanctioned_amount * $loan->interest_rate_pct / 100), 2),
        ]);
    }

    /**
     * POST /api/v1/loan/{id}/renew
     */
    public function renew(Request $request, Loan $loan): JsonResponse
    {
        $data = $request->validate([
            'interest_paid' => ['required', 'numeric', 'min:0'],
            'renewal_charges' => ['nullable', 'numeric', 'min:0'],
        ]);

        $tenure = $loan->loanProduct->tenure_months;
        $newDueDate = now()->addMonths($tenure);

        $renewal = LoanRenewal::create([
            'loan_id' => $loan->id,
            'renewed_tenure_months' => $tenure,
            'interest_paid' => $data['interest_paid'],
            'renewal_charges' => $data['renewal_charges'] ?? 0,
            'new_due_date' => $newDueDate,
            'processed_by' => $request->user()->id,
        ]);

        $loan->update(['status' => 'RENEWED', 'due_date' => $newDueDate]);

        return response()->json(['data' => $renewal], 201);
    }
}
