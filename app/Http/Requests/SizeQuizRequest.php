<?php

namespace App\Http\Requests;

use App\Actions\SizeQuiz\RecommendSize;
use App\Enums\Gender;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SizeQuizRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'gender' => ['required', Rule::enum(Gender::class)],
            'waist_cm' => ['required', 'integer', 'min:60', 'max:130'],
            'height_cm' => ['required', 'integer', 'min:140', 'max:210'],
            'hips' => ['required', Rule::in(RecommendSize::HIPS)],
            'fit' => ['required', Rule::in(RecommendSize::FITS)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'gender' => __('storefront.fields.gender'),
            'waist_cm' => __('storefront.fields.waist_cm'),
            'height_cm' => __('storefront.fields.height_cm'),
            'hips' => __('storefront.fields.hips'),
            'fit' => __('storefront.fields.fit'),
        ];
    }
}
