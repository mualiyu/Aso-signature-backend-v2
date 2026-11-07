<?php

namespace Webkul\Shop\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Webkul\Customer\Data\MeasurementFields;

class MeasurementProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'            => ['required', 'string', 'max:100'],
            'gender'          => ['nullable', Rule::in([MeasurementFields::GENDER_MALE, MeasurementFields::GENDER_FEMALE])],
            'unit'            => ['nullable', Rule::in([MeasurementFields::UNIT_CM, MeasurementFields::UNIT_INCHES])],
            'fit_preference'  => ['nullable', Rule::in(array_keys(MeasurementFields::fitPreferenceOptions()))],
            'fit_notes'       => ['nullable', 'array'],
            'fit_notes.*'     => [Rule::in(array_keys(MeasurementFields::fitNoteOptions()))],
            'fit_notes_other' => ['nullable', 'string', 'max:500'],
            'is_default'      => ['nullable', 'boolean'],
        ];
    }
}
