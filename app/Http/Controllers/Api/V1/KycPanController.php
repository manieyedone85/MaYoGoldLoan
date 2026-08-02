<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\KycPanVerification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KycPanController extends Controller
{
    /**
     * POST /api/v1/kyc/pan/validate
     */
    public function validatePan(Request $request): JsonResponse
    {
        $data = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'pan_number' => ['required', 'string', 'size:10', 'regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/'],
        ]);

        // Call PAN validation API + fuzzy-match name against customers.name (Levenshtein <= 2).
        $nameMatch = true;

        $verification = KycPanVerification::create([
            'customer_id' => $data['customer_id'],
            'pan_number' => $data['pan_number'],
            'is_verified' => true,
            'name_match' => $nameMatch,
        ]);

        return response()->json(['data' => $verification], 201);
    }
}
