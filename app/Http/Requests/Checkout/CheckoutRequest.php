<?php

namespace App\Http\Requests\Checkout;

use App\Enums\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Commande en quatre champs : nom, mobile, ville, adresse.
 *
 * Le paiement à la livraison ne demande rien d'autre. Le reste (complément
 * d'adresse, remarques) se règle au téléphone ou sur WhatsApp à la confirmation,
 * et l'adresse e-mail n'est connue que des clients qui ont un compte.
 */
class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->input('name', '')),
            'phone' => preg_replace('/[\s.\-()]+/', '', (string) $this->input('phone', '')),
            'city' => trim((string) $this->input('city', '')),
            'line1' => trim((string) $this->input('line1', '')),
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
            'city' => ['required', 'string', 'min:2', 'max:80'],
            'line1' => ['required', 'string', 'min:5', 'max:190'],
            'payment_method' => ['required', Rule::enum(PaymentMethod::class)],
            'discount_code' => ['nullable', 'string', 'max:30'],
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
            'city' => __('storefront.fields.city'),
            'line1' => __('storefront.fields.line1'),
            'payment_method' => __('storefront.fields.payment_method'),
            'discount_code' => __('storefront.fields.discount_code'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'phone.regex' => __('storefront.checkout.phone_invalid'),
        ];
    }
}
