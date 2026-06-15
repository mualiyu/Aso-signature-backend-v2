<?php

namespace Webkul\Shop\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Webkul\Customer\Data\MeasurementFields;

class MeasurementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $gender = in_array($this->input('gender'), [MeasurementFields::GENDER_MALE, MeasurementFields::GENDER_FEMALE], true)
            ? $this->input('gender')
            : MeasurementFields::GENDER_FEMALE;

        $allowedSlugs = MeasurementFields::slugsForGender($gender);

        return [
            'gender' => ['required', Rule::in([MeasurementFields::GENDER_MALE, MeasurementFields::GENDER_FEMALE])],
            'unit'   => ['required', Rule::in([MeasurementFields::UNIT_CM, MeasurementFields::UNIT_INCHES])],
            'measurements' => ['nullable', 'array'],
            'measurements.upper_body' => ['nullable', 'array'],
            'measurements.lower_body' => ['nullable', 'array'],
            'measurements.upper_body.*' => ['nullable', 'numeric', 'min:0', 'max:500'],
            'measurements.lower_body.*' => ['nullable', 'numeric', 'min:0', 'max:500'],
            'custom' => ['nullable', 'array'],
            'custom.*.id' => ['nullable', 'integer'],
            'custom.*.name' => ['nullable', 'string', 'max:255'],
            'custom.*.value' => ['nullable', 'numeric', 'min:0', 'max:500'],
            'redirect' => ['nullable', 'string'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $gender = $this->input('gender');
            $allowedSlugs = MeasurementFields::slugsForGender((string) $gender);
            $measurements = $this->input('measurements', []);

            foreach (['upper_body', 'lower_body'] as $group) {
                foreach ((array) ($measurements[$group] ?? []) as $slug => $entry) {
                    if ($entry === null || $entry === '') {
                        continue;
                    }

                    if (! in_array($slug, $allowedSlugs, true)) {
                        $validator->errors()->add("measurements.{$group}.{$slug}", 'Invalid measurement field submitted.');
                    }
                }
            }
        });
    }
}
