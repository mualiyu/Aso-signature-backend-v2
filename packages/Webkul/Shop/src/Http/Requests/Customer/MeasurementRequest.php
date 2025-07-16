<?php

namespace Webkul\Shop\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;

class MeasurementRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            '*.name' => [
                'nullable',
                'string',
                'max:255'
            ],
            '*.value' => [
                'nullable',
                'numeric',
                'min:0'
            ],
            '*.unit' => [
                'nullable',
                'string',
                'in:cm,inches'
            ],
            '*.measurement_type' => [
                'nullable',
                'string',
                'in:bust,waist,hip,shoulder,arm_length,leg_length,inseam,neck,chest'
            ],
            '*.notes' => [
                'nullable',
                'string',
                'max:500'
            ]
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The measurement name is required',
            'value.required' => 'The measurement value is required',
            'value.numeric' => 'The measurement value must be a number',
            'value.min' => 'The measurement value cannot be negative',
            'unit.required' => 'Please specify the measurement unit',
            'unit.in' => 'The unit must be either cm or inches',
            'measurement_type.required' => 'Please specify the measurement type',
            'measurement_type.in' => 'Invalid measurement type selected'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'measurement name',
            'value' => 'measurement value',
            'unit' => 'measurement unit',
            'measurement_type' => 'measurement type',
            'notes' => 'notes'
        ];
    }
}
