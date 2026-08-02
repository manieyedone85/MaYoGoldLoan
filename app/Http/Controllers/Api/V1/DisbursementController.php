<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Loan;
use App\Models\LoanDisbursement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DisbursementController extends Controller
{
    // Cash disbursement above this (INR) is blocked -- forces a bank-transfer mode instead.
    private const CASH_LIMIT = 20000;

    /**
     * POST /api/v1/loan/{id}/disburse
     */
    public function disburse(Request $request, Loan $loan): JsonResponse
    {
        abort_if($loan->status !== 'APPROVED', 422, 'Loan must be approved before disbursement.');

        $data = $request->validate([
            'mode' => ['required', 'in:CASH,IMPS,RTGS,NEFT,UPI,BANK_TRANSFER'],
            'reference_number' => ['nullable', 'string'],
        ]);

        if ($data['mode'] === 'CASH' && $loan->net_disbursed_amount > self::CASH_LIMIT) {
            return response()->json([
                'message' => 'Cash disbursement above the regulatory limit is not permitted. Use a bank transfer mode.',
            ], 422);
        }

        $disbursement = LoanDisbursement::create([
            'loan_id' => $loan->id,
            'mode' => $data['mode'],
            'amount' => $loan->net_disbursed_amount,
            'reference_number' => $data['reference_number'] ?? null,
            'status' => 'COMPLETED',
            'disbursed_by' => $request->user()->id,
        ]);

        $loan->update(['status' => 'ACTIVE']);

        // Auto GL posting -- every money-moving transaction posts to accounting, never manual double-entry.
        // AccountingService::postDisbursement($loan, $disbursement);

        return response()->json(['data' => $disbursement], 201);
    }
}
