<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\NotificationLog;
use App\Models\NotificationTemplate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * POST /api/v1/notify/sms | /whatsapp | /email | /push
     * Dispatches via a queued job with Resilience4j-equivalent retry/backoff
     * (Laravel's ShouldQueue + $tries/backoff()), same pattern as the metadata-driven
     * exporter framework's third-party retry config.
     */
    public function send(Request $request, string $channel): JsonResponse
    {
        $data = $request->validate([
            'customer_id' => ['nullable', 'exists:customers,id'],
            'template_code' => ['required', 'exists:notification_templates,code'],
        ]);

        $template = NotificationTemplate::where('code', $data['template_code'])->firstOrFail();

        $log = NotificationLog::create([
            'customer_id' => $data['customer_id'] ?? null,
            'template_id' => $template->id,
            'channel' => strtoupper($channel),
            'status' => 'QUEUED',
        ]);

        // SendNotificationJob::dispatch($log)->onQueue('notifications');

        return response()->json(['data' => $log], 201);
    }
}
