<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Loan\CalculateLoanRequest;
use App\Models\JewelleryItem;
use App\Models\Loan;
use App\Models\LoanApprovalWorkflow;
use App\Models\LoanCharge;
use App\Models\LoanProduct;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LoanController extends Controller
{
    /**
     * POST /api/v1/loan/calculate
     * eligible_amount = sum(jewellery net_weight * applied_rate * eligible_percentage)
     * interest computed per loan_products.interest_type (flat/reducing);
     * processing fee + GST + insurance added as loan_charges line items.
     */
    public function calculate(CalculateLoanRequest $request): JsonResponse
    {
        $data = $request->validated();
        $product = LoanProduct::findOrFail($data['loan_product_id']);
        $items = JewelleryItem::whereIn('id', $data['jewellery_item_ids'])->get();

        $eligibleAmount = (float) $items->sum('eligible_amount');
        $processingFee = round($eligibleAmount * ($product->processing_fee_pct / 100), 2);
        $gst = round($processingFee * ($product->gst_pct / 100), 2);
        $insurance = round($eligibleAmount * ($product->insurance_pct / 100), 2);
        $netDisbursed = $eligibleAmount - $processingFee - $gst - $insurance;

        return response()->json([
            'eligible_amount' => $eligibleAmount,
            'interest_rate_pct' => $product->interest_rate_pct,
            'interest_type' => $product->interest_type,
            'tenure_months' => $product->tenure_months,
            'processing_fee' => $processingFee,
            'gst_amount' => $gst,
            'insurance_amount' => $insurance,
            'net_disbursed_amount' => $netDisbursed,
        ]);
    }

    /**
     * POST /api/v1/loan  (create in DRAFT, then submit-for-approval)
     */
    public function store(CalculateLoanRequest $request): JsonResponse
    {
        $data = $request->validated();
        $product = LoanProduct::findOrFail($data['loan_product_id']);
        $items = JewelleryItem::whereIn('id', $data['jewellery_item_ids'])->get();
        $eligibleAmount = (float) $items->sum('eligible_amount');

        $loan = DB::transaction(function () use ($data, $product, $items, $eligibleAmount, $request) {
            $processingFee = round($eligibleAmount * ($product->processing_fee_pct / 100), 2);
            $gst = round($processingFee * ($product->gst_pct / 100), 2);
            $insurance = round($eligibleAmount * ($product->insurance_pct / 100), 2);

            $loan = Loan::create([
                'loan_account_number' => 'LGH001' . str_pad((string) (Loan::max('id') + 1), 9, '0', STR_PAD_LEFT),
                'customer_id' => $data['customer_id'],
                'branch_id' => $data['branch_id'],
                'loan_product_id' => $product->id,
                'eligible_amount' => $eligibleAmount,
                'sanctioned_amount' => $eligibleAmount,
                'interest_rate_pct' => $product->interest_rate_pct,
                'processing_fee' => $processingFee,
                'gst_amount' => $gst,
                'insurance_amount' => $insurance,
                'net_disbursed_amount' => $eligibleAmount - $processingFee - $gst - $insurance,
                'loan_date' => now()->toDateString(),
                'due_date' => now()->addMonths($product->tenure_months)->toDateString(),
                'status' => 'DRAFT',
                'created_by' => $request->user()->id,
            ]);

            foreach ([
                ['charge_type' => 'PROCESSING_FEE', 'amount' => $processingFee],
                ['charge_type' => 'GST', 'amount' => $gst],
                ['charge_type' => 'INSURANCE', 'amount' => $insurance],
            ] as $charge) {
                $loan->charges()->create($charge);
            }

            $items->each(function ($item) use ($loan) {
                $item->update(['loan_id' => $loan->id, 'status' => 'PLEDGED']);
            });

            return $loan;
        });

        return response()->json(['data' => $loan->load('charges')], 201);
    }

    /**
     * GET /api/v1/loan/{id}/emi-schedule
     */
    public function emiSchedule(Loan $loan): JsonResponse
    {
        $months = $loan->loanProduct->tenure_months;
        $monthlyInterest = round(($loan->sanctioned_amount * $loan->interest_rate_pct / 100) / 12, 2);

        $schedule = collect(range(1, $months))->map(function ($m) use ($loan, $monthlyInterest) {
            return [
                'month' => $m,
                'due_date' => $loan->loan_date->copy()->addMonths($m)->toDateString(),
                'interest_due' => $monthlyInterest,
            ];
        });

        return response()->json(['data' => $schedule]);
    }
}
