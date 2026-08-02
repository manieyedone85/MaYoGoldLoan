<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\LoanProduct;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Generic master-data CRUD. Reuses the Team Ops Portal's existing pattern:
 * /api/v1/master/{entity} routed dynamically per entity below.
 */
class MasterController extends Controller
{
    public function branches(): JsonResponse
    {
        return response()->json(['data' => Branch::all()]);
    }

    public function storeBranch(Request $request): JsonResponse
    {
        $data = $request->validate([
            'branch_code' => ['required', 'string', 'unique:branches'],
            'name' => ['required', 'string'],
            'city' => ['nullable', 'string'],
            'state' => ['nullable', 'string'],
        ]);

        return response()->json(['data' => Branch::create($data)], 201);
    }

    public function loanProducts(): JsonResponse
    {
        return response()->json(['data' => LoanProduct::all()]);
    }

    public function storeLoanProduct(Request $request): JsonResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'unique:loan_products'],
            'name' => ['required', 'string'],
            'interest_rate_pct' => ['required', 'numeric'],
            'interest_type' => ['required', 'in:FLAT,REDUCING'],
            'tenure_months' => ['required', 'integer', 'min:1'],
            'processing_fee_pct' => ['nullable', 'numeric'],
            'gst_pct' => ['nullable', 'numeric'],
            'insurance_pct' => ['nullable', 'numeric'],
        ]);

        return response()->json(['data' => LoanProduct::create($data)], 201);
    }

    public function roles(): JsonResponse
    {
        return response()->json(['data' => Role::with('permissions')->get()]);
    }
}
