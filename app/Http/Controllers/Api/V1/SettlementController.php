<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Loan;
use App\Models\LoanClosure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettlementController extends Controller
{
    /**
     * GET /api/v1/loan/{id}/closure-statement
     */
    public function closureStatement(Loan $loan): JsonResponse
    {
        $interestPaid = (float) $loan->interestCollections()->sum('amount');

        return response()->json([
            'sanctioned_amount' => $loan->sanctioned_amount,
            'interest_paid' => $interestPaid,
            'total_payable_to_close' => $loan->sanctioned_amount, // + any pending interest, per current schedule
        ]);
    }

    /**
     * POST /api/v1/loan/{id}/settle
     */
    public function settle(Request $request, Loan $loan): JsonResponse
    {
        $data = $request->validate(['total_amount_collected' => ['required', 'numeric', 'min:0']]);

        $closure = LoanClosure::create([
            'loan_id' => $loan->id,
            'total_amount_collected' => $data['total_amount_collected'],
            'closure_date' => now()->toDateString(),
            'closed_by' => $request->user()->id,
        ]);

        $loan->update(['status' => 'SETTLED']);
        $loan->jewelleryItems()->update(['status' => 'RELEASED']);

        return response()->json(['data' => $closure], 201);
    }
}
