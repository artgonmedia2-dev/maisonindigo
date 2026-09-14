<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StockAlertRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'variant_id' => ['required', 'integer', 'exists:product_variants,id'],
            'email' => ['required_without:phone', 'nullable', 'email:rfc', 'max:190'],
            'phone' => ['required_without:email', 'nullable', 'string', 'regex:/^(?:\+212|00212|0)[5-7]\d{8}$/'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'variant_id' => __('storefront.fields.variant'),
            'email' => __('storefront.fields.email'),
            'phone' => __('storefront.fields.phone'),
        ];
    }
}
