<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'mobile' => ['required', 'string', 'size:10'],
            'password' => ['required_without:mpin', 'string'],
            'mpin' => ['required_without:password', 'string', 'size:6'],
            'device_id' => ['required', 'string'],
        ];
    }
}
