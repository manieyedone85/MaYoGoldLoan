<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Loan;
use App\Models\LoanPartPayment;
use App\Models\LoanReload;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PartPaymentController extends Controller
{
    /**
     * POST /api/v1/loan/{id}/part-payment
     */
    public function partPayment(Request $request, Loan $loan): JsonResponse
    {
        $data = $request->validate([
            'principal_amount' => ['nullable', 'numeric', 'min:0'],
            'interest_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $payment = LoanPartPayment::create([
            'loan_id' => $loan->id,
            'principal_amount' => $data['principal_amount'] ?? 0,
            'interest_amount' => $data['interest_amount'] ?? 0,
            'collected_by' => $request->user()->id,
        ]);

        if (($data['principal_amount'] ?? 0) > 0) {
            $loan->update([
                'sanctioned_amount' => $loan->sanctioned_amount - $data['principal_amount'],
                'status' => 'PART_PAID',
            ]);
        }

        return response()->json(['data' => $payment], 201);
    }

    /**
     * POST /api/v1/loan/{id}/reload
     * Excess-amount reload after part payment reduces the pledge value needed.
     */
    public function reload(Request $request, Loan $loan): JsonResponse
    {
        $data = $request->validate([
            'excess_amount_eligible' => ['required', 'numeric', 'min:0'],
            'reload_amount' => ['required', 'numeric', 'min:0'],
        ]);

        $reload = LoanReload::create(array_merge($data, [
            'loan_id' => $loan->id,
            'processed_by' => $request->user()->id,
        ]));

        $loan->update(['sanctioned_amount' => $loan->sanctioned_amount + $data['reload_amount']]);

        return response()->json(['data' => $reload], 201);
    }
}
