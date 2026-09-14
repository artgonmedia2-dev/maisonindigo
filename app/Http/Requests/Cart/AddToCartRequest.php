<?php

namespace App\Http\Requests\Cart;

use Illuminate\Foundation\Http\FormRequest;

class AddToCartRequest extends FormRequest
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
            'qty' => ['sometimes', 'integer', 'min:1', 'max:5'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'variant_id' => __('storefront.fields.variant'),
            'qty' => __('storefront.fields.qty'),
        ];
    }
}
