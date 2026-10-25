<?php

namespace Webkul\Alteration\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Webkul\Alteration\Models\AlterationPartner;

class PartnerRequest extends FormRequest
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
            'name'                     => ['required', 'string', 'max:255'],
            'contact_name'             => ['nullable', 'string', 'max:255'],
            'email'                    => ['nullable', 'email', 'max:255'],
            'phone'                    => ['nullable', 'string', 'max:64'],
            'whatsapp'                 => ['nullable', 'string', 'max:64'],
            'website'                  => ['nullable', 'string', 'max:255'],
            'address'                  => ['nullable', 'string', 'max:255'],
            'city'                     => ['required', 'string', 'max:120'],
            'country'                  => ['required', 'string', 'size:2'],
            'latitude'                 => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'                => ['nullable', 'numeric', 'between:-180,180'],
            'outreach_status'          => ['required', Rule::in(config('alteration.outreach_statuses'))],
            'sourced_by'               => ['nullable', 'string', 'max:255'],
            'vetting_status'           => ['required', Rule::in(config('alteration.vetting_statuses'))],
            'vetting_checklist'        => ['nullable', 'array'],
            'vetting_checklist.*'      => [Rule::in(config('alteration.vetting_checklist'))],
            'is_active'                => ['nullable', 'boolean'],
            'currency'                 => ['nullable', 'string', 'size:3'],
            'side_conditions'          => ['nullable', 'string'],
            'payment_terms'            => ['nullable', 'string'],
            'notes'                    => ['nullable', 'string'],
            'prices'                   => ['nullable', 'array'],
            'prices.*.price'           => ['nullable', 'numeric', 'min:0'],
            'prices.*.turnaround_days' => ['nullable', 'integer', 'min:0', 'max:365'],
            'prices.*.notes'           => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * A partner can only be vetted once every checklist item is ticked and it has map coordinates
     * (the tailor locator needs them), and can only be live once vetted.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $vetted = $this->input('vetting_status') === AlterationPartner::VETTING_VETTED;

            if ($vetted && ! AlterationPartner::checklistIsComplete((array) $this->input('vetting_checklist', []))) {
                $validator->errors()->add('vetting_status', trans('alteration::app.admin.partners.checklist-incomplete'));
            }

            if ($vetted && ($this->input('latitude') === null || $this->input('longitude') === null)) {
                $validator->errors()->add('latitude', trans('alteration::app.admin.partners.coordinates-required'));
            }

            if ($this->boolean('is_active') && ! $vetted) {
                $validator->errors()->add('is_active', trans('alteration::app.admin.partners.active-requires-vetted'));
            }
        });
    }

    /**
     * Normalise inputs before validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'country'           => strtoupper((string) $this->input('country')),
            'currency'          => $this->filled('currency') ? strtoupper((string) $this->input('currency')) : null,
            'is_active'         => $this->boolean('is_active'),
            'vetting_checklist' => array_values((array) $this->input('vetting_checklist', [])),
        ]);
    }
}
