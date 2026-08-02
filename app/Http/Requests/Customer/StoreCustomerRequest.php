<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasAnyRole(['BRANCH_EXECUTIVE', 'BRANCH_MANAGER', 'ADMIN']);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'mobile' => ['required', 'string', 'size:10'],
            'email' => ['nullable', 'email', 'max:150'],
            'dob' => ['nullable', 'date'],
            'gender' => ['nullable', 'in:MALE,FEMALE,OTHER'],
            'branch_id' => ['required', 'exists:branches,id'],
            'address' => ['required', 'array'],
            'address.line1' => ['required', 'string', 'max:255'],
            'address.city' => ['required', 'string', 'max:100'],
            'address.state' => ['required', 'string', 'max:100'],
            'address.pincode' => ['required', 'string', 'max:10'],
        ];
    }
}
