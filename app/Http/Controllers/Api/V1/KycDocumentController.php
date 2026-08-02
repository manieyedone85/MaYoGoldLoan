<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\KycDocument;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KycDocumentController extends Controller
{
    /**
     * POST /api/v1/kyc/document
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'document_type_id' => ['required', 'exists:kyc_document_types,id'],
            'file' => ['required', 'file', 'max:5120'],
        ]);

        $path = $request->file('file')->store('kyc-documents');

        $document = KycDocument::create([
            'customer_id' => $data['customer_id'],
            'document_type_id' => $data['document_type_id'],
            'file_ref' => $path,
            'status' => 'PENDING',
        ]);

        return response()->json(['data' => $document], 201);
    }

    /**
     * GET /api/v1/kyc/document/{customerId}
     */
    public function index(int $customerId): JsonResponse
    {
        return response()->json([
            'data' => KycDocument::where('customer_id', $customerId)->get(),
        ]);
    }

    /**
     * PUT /api/v1/kyc/document/{id}/verify
     */
    public function verify(Request $request, KycDocument $kycDocument): JsonResponse
    {
        $request->validate(['status' => ['required', 'in:VERIFIED,REJECTED']]);

        $kycDocument->update([
            'status' => $request->input('status'),
            'verified_by' => $request->user()->id,
        ]);

        return response()->json(['data' => $kycDocument]);
    }
}
