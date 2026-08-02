<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\SyncQueue;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SyncController extends Controller
{
    /**
     * POST /api/v1/sync/upload-queue
     * Conflict resolution: last-write-wins for non-financial fields; server-wins
     * with manual-review flag for financial fields (loan amount, payments) --
     * never a silent overwrite on money fields.
     */
    public function uploadQueue(Request $request): JsonResponse
    {
        $data = $request->validate([
            'entity_type' => ['required', 'string'],
            'payload' => ['required', 'array'],
        ]);

        $queued = SyncQueue::create([
            'user_id' => $request->user()->id,
            'entity_type' => $data['entity_type'],
            'payload' => $data['payload'],
            'status' => 'PENDING',
        ]);

        return response()->json(['data' => $queued], 201);
    }

    /** GET /api/v1/sync/download-delta?lastSyncTs= */
    public function downloadDelta(Request $request): JsonResponse
    {
        $since = $request->query('lastSyncTs', now()->subDay()->toDateTimeString());

        return response()->json([
            'loans' => \App\Models\Loan::where('updated_at', '>=', $since)->get(),
            'customers' => \App\Models\Customer::where('updated_at', '>=', $since)->get(),
        ]);
    }
}
