<?php

namespace App\Http\Requests\Jewellery;

use Illuminate\Foundation\Http\FormRequest;

class EvaluateJewelleryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('APPRAISER');
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'exists:customers,id'],
            'category_id' => ['required', 'exists:jewellery_categories,id'],
            'hallmark_flag' => ['boolean'],
            'gross_weight' => ['required', 'numeric', 'min:0.001'],
            'stone_weight' => ['nullable', 'numeric', 'min:0'],
            'purity_karat' => ['required', 'string', 'max:5'],
        ];
    }
}
