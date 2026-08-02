<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\CustomerLedger;
use App\Models\Voucher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccountingController extends Controller
{
    /** POST /api/v1/accounting/voucher */
    public function storeVoucher(Request $request): JsonResponse
    {
        $data = $request->validate([
            'branch_id' => ['required', 'exists:branches,id'],
            'type' => ['required', 'in:RECEIPT,PAYMENT,JOURNAL,CONTRA'],
            'voucher_date' => ['required', 'date'],
            'lines' => ['required', 'array', 'min:2'], // double-entry: at least debit + credit
            'lines.*.gl_account_id' => ['required', 'exists:gl_accounts,id'],
            'lines.*.debit' => ['nullable', 'numeric', 'min:0'],
            'lines.*.credit' => ['nullable', 'numeric', 'min:0'],
        ]);

        $totalDebit = collect($data['lines'])->sum('debit');
        $totalCredit = collect($data['lines'])->sum('credit');
        abort_if(round($totalDebit, 2) !== round($totalCredit, 2), 422, 'Voucher must balance: total debit must equal total credit.');

        $voucher = DB::transaction(function () use ($data, $request) {
            $voucher = Voucher::create([
                'voucher_number' => 'VCH' . now()->format('Ymd') . str_pad((string) (Voucher::max('id') + 1), 6, '0', STR_PAD_LEFT),
                'branch_id' => $data['branch_id'],
                'type' => $data['type'],
                'voucher_date' => $data['voucher_date'],
                'created_by' => $request->user()->id,
            ]);

            foreach ($data['lines'] as $line) {
                $voucher->details()->create($line);
            }

            return $voucher;
        });

        return response()->json(['data' => $voucher->load('details')], 201);
    }

    /** GET /api/v1/accounting/ledger/{accountId} */
    public function customerLedger(int $customerId): JsonResponse
    {
        return response()->json([
            'data' => CustomerLedger::where('customer_id', $customerId)->orderBy('created_at')->get(),
        ]);
    }
}
