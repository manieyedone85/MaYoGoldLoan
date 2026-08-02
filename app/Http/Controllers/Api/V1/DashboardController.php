<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\InterestCollection;
use App\Models\Loan;
use App\Models\LoanDisbursement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * GET /api/v1/dashboard/summary?branchId=&date=
     * Backed by aggregation queries here; move to a DB view (v_dashboard_daily_summary)
     * once traffic grows, to avoid hitting transactional tables directly.
     */
    public function summary(Request $request): JsonResponse
    {
        $data = $request->validate([
            'branchId' => ['required', 'exists:branches,id'],
            'date' => ['nullable', 'date'],
        ]);
        $date = $data['date'] ?? now()->toDateString();

        return response()->json([
            'todays_loans' => Loan::where('branch_id', $data['branchId'])->whereDate('loan_date', $date)->count(),
            'todays_collection' => InterestCollection::whereHas('loan', function ($q) use ($data) {
                $q->where('branch_id', $data['branchId']);
            })->whereDate('created_at', $date)->sum('amount'),
            'todays_disbursement' => LoanDisbursement::whereHas('loan', function ($q) use ($data) {
                $q->where('branch_id', $data['branchId']);
            })->whereDate('created_at', $date)->sum('amount'),
            'pending_approval' => Loan::where('branch_id', $data['branchId'])->where('status', 'PENDING_APPROVAL')->count(),
            'active_portfolio' => Loan::where('branch_id', $data['branchId'])->where('status', 'ACTIVE')->sum('sanctioned_amount'),
        ]);
    }
}
