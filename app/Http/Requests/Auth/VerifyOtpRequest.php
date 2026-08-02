<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class VerifyOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'mobile' => ['required', 'string', 'size:10'],
            'otp' => ['required', 'string', 'size:6'],
            'purpose' => ['required', 'in:LOGIN,FORGOT_PASSWORD,MPIN_RESET'],
            'device_id' => ['required_if:purpose,LOGIN', 'string'],
        ];
    }
}
