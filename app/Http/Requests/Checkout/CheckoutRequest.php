<?php

namespace App\Http\Requests\Checkout;

use App\Enums\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'phone' => preg_replace('/[\s.\-()]+/', '', (string) $this->input('phone', '')),
            'discount_code' => mb_strtoupper(trim((string) $this->input('discount_code', ''))),
        ]);
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:120'],
            // Mobile marocain : 06/07 ou +2126/+2127, 9 chiffres après l'indicatif.
            'phone' => ['required', 'string', 'regex:/^(?:\+212|00212|0)[5-7]\d{8}$/'],
            'email' => ['nullable', 'email:rfc', 'max:190'],
            'line1' => ['required', 'string', 'min:5', 'max:190'],
            'line2' => ['nullable', 'string', 'max:190'],
            'city' => ['required', 'string', 'min:2', 'max:80'],
            'region' => ['nullable', 'string', 'max:80'],
            'payment_method' => ['required', Rule::enum(PaymentMethod::class)],
            'discount_code' => ['nullable', 'string', 'max:30'],
            'notes' => ['nullable', 'string', 'max:500'],
            'accept_terms' => ['accepted'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => __('storefront.fields.name'),
            'phone' => __('storefront.fields.phone'),
            'email' => __('storefront.fields.email'),
            'line1' => __('storefront.fields.line1'),
            'line2' => __('storefront.fields.line2'),
            'city' => __('storefront.fields.city'),
            'region' => __('storefront.fields.region'),
            'payment_method' => __('storefront.fields.payment_method'),
            'discount_code' => __('storefront.fields.discount_code'),
            'notes' => __('storefront.fields.notes'),
            'accept_terms' => __('storefront.fields.accept_terms'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'phone.regex' => __('storefront.checkout.phone_invalid'),
            'accept_terms.accepted' => __('storefront.checkout.terms_required'),
        ];
    }
}
