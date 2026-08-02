<?php

namespace App\Http\Requests\Loan;

use Illuminate\Foundation\Http\FormRequest;

class CalculateLoanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'exists:customers,id'],
            'branch_id' => ['required', 'exists:branches,id'],
            'loan_product_id' => ['required', 'exists:loan_products,id'],
            'jewellery_item_ids' => ['required', 'array', 'min:1'],
            'jewellery_item_ids.*' => ['exists:jewellery_items,id'],
        ];
    }
}
