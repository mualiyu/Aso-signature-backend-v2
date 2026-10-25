<?php

namespace Webkul\Alteration\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreClaimRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $min = (int) config('alteration.min_photos');

        return [
            'reason'      => ['required', Rule::in(config('alteration.claim_reasons'))],
            'description' => ['required', 'string', 'max:2000'],
            'garment'     => ['nullable', 'array'],
            'garment.*'   => ['nullable', 'numeric', 'min:0', 'max:999'],
            'photos'      => ['required', 'array', 'min:'.$min, 'max:'.(int) config('alteration.max_photos')],
            'photos.*'    => ['file', 'image', 'max:'.(int) config('alteration.max_photo_size')],
        ];
    }

    public function messages(): array
    {
        return [
            'photos.required' => trans('alteration::app.shop.claims.too-few-photos', ['min' => config('alteration.min_photos')]),
            'photos.min'      => trans('alteration::app.shop.claims.too-few-photos', ['min' => config('alteration.min_photos')]),
        ];
    }

    /**
     * A measurement-mismatch claim must include the comparison; material and workmanship claims
     * rely on the photos.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($this->input('reason') !== 'measurement_mismatch') {
                return;
            }

            $entered = collect($this->input('garment', []))->filter(fn ($value) => $value !== null && $value !== '');

            if ($entered->isEmpty()) {
                $validator->errors()->add('garment', trans('alteration::app.shop.claims.measurement-required'));
            }
        });
    }
}
