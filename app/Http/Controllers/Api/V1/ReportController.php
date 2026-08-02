<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Loan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Single generic parameterized endpoint rather than 100+ separate report endpoints.
 * Build out report_code handlers incrementally, starting with the top 15-20
 * highest-value reports (daily cash, NPA, branch performance, auction, GST/audit).
 */
class ReportController extends Controller
{
    /** GET /api/v1/reports/{reportCode}?params= */
    public function generate(Request $request, string $reportCode): JsonResponse
    {
        if ($reportCode === 'DAILY_CASH') {
            return $this->dailyCash($request);
        }

        if ($reportCode === 'NPA') {
            return $this->npa($request);
        }

        return response()->json(['message' => 'Unknown or not-yet-implemented report code.'], 404);
    }

    private function dailyCash(Request $request): JsonResponse
    {
        $branchId = $request->query('branchId');
        $date = $request->query('date', now()->toDateString());

        return response()->json([
            'report' => 'DAILY_CASH',
            'branch_id' => $branchId,
            'date' => $date,
            // aggregate cash disbursements + collections for the branch/date here
        ]);
    }

    private function npa(Request $request): JsonResponse
    {
        $loans = Loan::where('status', 'NPA')->with('customer', 'branch')->get();

        return response()->json(['report' => 'NPA', 'data' => $loans]);
    }
}
