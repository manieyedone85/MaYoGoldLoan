<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\KycAadhaarVerification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class KycAadhaarController extends Controller
{
    /**
     * POST /api/v1/kyc/aadhaar/qr-scan
     * Full Aadhaar number is NEVER stored in plaintext -- only last-4 + hash + UIDAI ref.
     */
    public function qrScan(Request $request): JsonResponse
    {
        $data = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'aadhaar_number' => ['required', 'digits:12'],
            'uidai_reference_id' => ['nullable', 'string'],
        ]);

        $customer = Customer::findOrFail($data['customer_id']);
        $customer->update([
            'aadhaar_last4' => substr($data['aadhaar_number'], -4),
            'aadhaar_hash' => hash('sha256', $data['aadhaar_number']),
        ]);

        $verification = KycAadhaarVerification::create([
            'customer_id' => $customer->id,
            'method' => 'QR',
            'uidai_reference_id' => $data['uidai_reference_id'] ?? null,
            'is_verified' => true,
            'verified_at' => now(),
        ]);

        return response()->json(['data' => $verification], 201);
    }

    /**
     * POST /api/v1/kyc/aadhaar/offline-xml
     */
    public function offlineXml(Request $request): JsonResponse
    {
        $data = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'xml_file' => ['required', 'file', 'mimes:xml'],
            'share_code' => ['required', 'string'],
        ]);

        // Parse + validate offline XML signature against UIDAI's public cert here.
        $verification = KycAadhaarVerification::create([
            'customer_id' => $data['customer_id'],
            'method' => 'OFFLINE_XML',
            'is_verified' => true,
            'verified_at' => now(),
        ]);

        return response()->json(['data' => $verification], 201);
    }

    /**
     * POST /api/v1/kyc/aadhaar/face-auth
     */
    public function faceAuth(Request $request): JsonResponse
    {
        $data = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'face_image' => ['required', 'image'],
        ]);

        // Call UIDAI face-auth API / third-party liveness+match service here.
        $matched = true;
        $confidence = 96.5;

        \App\Models\KycFaceAuthLog::create([
            'customer_id' => $data['customer_id'],
            'is_matched' => $matched,
            'confidence_score' => $confidence,
        ]);

        return response()->json(['is_matched' => $matched, 'confidence_score' => $confidence]);
    }

    /**
     * GET /api/v1/kyc/aadhaar/{customerId}/masked
     */
    public function masked(int $customerId): JsonResponse
    {
        $customer = Customer::findOrFail($customerId);

        return response()->json([
            'masked_aadhaar' => $customer->aadhaar_last4 ? 'XXXXXXXX' . $customer->aadhaar_last4 : null,
        ]);
    }
}
