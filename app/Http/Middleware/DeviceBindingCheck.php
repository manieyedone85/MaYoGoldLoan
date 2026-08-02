<?php

namespace App\Http\Middleware;

use App\Models\UserDeviceBinding;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Field roles (Branch Executive, Appraiser, Cashier) are restricted to one
 * active bound device. Admin/Manager roles allow multiple devices.
 */
class DeviceBindingCheck
{
    private const SINGLE_DEVICE_ROLES = ['BRANCH_EXECUTIVE', 'APPRAISER', 'CASHIER'];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $deviceId = $request->header('X-Device-Id');

        $roleCode = $user && $user->role ? $user->role->code : null;
        if ($user && in_array($roleCode, self::SINGLE_DEVICE_ROLES, true) && $deviceId) {
            $bound = \App\Models\UserDeviceBinding::where('user_id', $user->id)
                ->where('is_active', true)
                ->first();

            if ($bound && $bound->device_id !== $deviceId) {
                return response()->json([
                    'message' => 'This account is bound to a different device. Contact your manager to rebind.',
                ], 409);
            }
        }

        return $next($request);
    }
}
