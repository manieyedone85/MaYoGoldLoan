<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class DeviceBindRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'device_id' => ['required', 'string'],
            'device_model' => ['nullable', 'string'],
            'push_token' => ['nullable', 'string'],
        ];
    }
}
