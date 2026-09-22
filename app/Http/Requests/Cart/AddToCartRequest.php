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
     * Les lignes à ajouter, que la demande porte un jean ou un lot.
     *
     * @return list<array{variant_id: int, qty: int}>
     */
    public function lines(): array
    {
        /** @var list<array{variant_id?: int|string, qty?: int|string}> $items */
        $items = $this->input('items', []);

        if ($items === []) {
            return [['variant_id' => $this->integer('variant_id'), 'qty' => max(1, $this->integer('qty', 1))]];
        }

        return array_values(array_map(fn (array $item): array => [
            'variant_id' => (int) ($item['variant_id'] ?? 0),
            'qty' => max(1, (int) ($item['qty'] ?? 1)),
        ], $items));
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            // Un jean seul, ou un lot : le lot envoie ses lignes d'un coup pour
            // que le panier ne parte jamais à moitié constitué.
            'variant_id' => ['required_without:items', 'integer', 'exists:product_variants,id'],
            'qty' => ['sometimes', 'integer', 'min:1', 'max:5'],
            'items' => ['required_without:variant_id', 'array', 'min:1', 'max:5'],
            'items.*.variant_id' => ['required', 'integer', 'exists:product_variants,id'],
            'items.*.qty' => ['sometimes', 'integer', 'min:1', 'max:5'],
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
            'items.*.variant_id' => __('storefront.fields.variant'),
            'items.*.qty' => __('storefront.fields.qty'),
        ];
    }
}
