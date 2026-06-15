<?php

namespace Webkul\Customer\Services;

use Illuminate\Support\Str;
use Webkul\Customer\Contracts\Customer;
use Webkul\Customer\Data\MeasurementFields;
use Webkul\Customer\Models\Measurement;

class MeasurementService
{
    /**
     * Build payload for the measurement form / API.
     */
    public function buildFormPayload(Customer $customer): array
    {
        $profileGender = MeasurementFields::resolveGender($customer->gender);
        $saved = $this->indexedMeasurements($customer);

        $values = [];
        $custom = [];

        foreach ($saved as $key => $measurement) {
            if ($measurement->measurement_type === MeasurementFields::GROUP_CUSTOM) {
                $custom[] = [
                    'id'    => $measurement->id,
                    'name'  => $measurement->notes ?: $measurement->name,
                    'value' => $measurement->value,
                ];

                continue;
            }

            $values[$measurement->name] = [
                'value' => $measurement->value,
                'group' => $measurement->measurement_type,
            ];
        }

        $unit = MeasurementFields::UNIT_INCHES;

        $first = $customer->measurements()->whereNotNull('unit')->first();

        if ($first) {
            $unit = MeasurementFields::normalizeUnit($first->unit);
        }

        $gender = $savedGender = $customer->measurements()
            ->whereIn('gender', [MeasurementFields::GENDER_MALE, MeasurementFields::GENDER_FEMALE])
            ->value('gender');

        $activeGender = $savedGender ?: $profileGender;

        return [
            'gender'       => $activeGender,
            'profileGender'=> $profileGender,
            'unit'         => $unit,
            'values'       => $values,
            'custom'       => $custom,
            'fields'       => MeasurementFields::definitions(),
            'groupLabels'  => MeasurementFields::groupLabels(),
            'completeness' => $this->completeness($activeGender, $values),
        ];
    }

    /**
     * Persist measurements from form/API payload.
     */
    public function save(Customer $customer, array $payload): array
    {
        $gender = in_array($payload['gender'] ?? null, [MeasurementFields::GENDER_MALE, MeasurementFields::GENDER_FEMALE], true)
            ? $payload['gender']
            : MeasurementFields::resolveGender($customer->gender);

        $unit = MeasurementFields::normalizeUnit($payload['unit'] ?? MeasurementFields::UNIT_INCHES);
        $measurements = $payload['measurements'] ?? [];
        $custom = $payload['custom'] ?? [];
        $allowedFields = MeasurementFields::fieldsForGender($gender);
        $savedSlugs = [];

        foreach ($allowedFields as $slug => $field) {
            $value = data_get($measurements, "{$field['group']}.{$slug}.value")
                ?? data_get($measurements, "{$field['group']}.{$slug}");

            if ($value === null || $value === '') {
                continue;
            }

            Measurement::updateOrCreate(
                [
                    'customer_id'      => $customer->id,
                    'name'             => $slug,
                    'measurement_type' => $field['group'],
                ],
                [
                    'value'  => $value,
                    'unit'   => $unit,
                    'gender' => $gender,
                    'notes'  => null,
                ]
            );

            $savedSlugs[] = $slug;
        }

        $customIds = [];

        foreach ($custom as $entry) {
            $name = trim((string) ($entry['name'] ?? ''));

            if ($name === '' || ! isset($entry['value']) || $entry['value'] === '') {
                continue;
            }

            $slug = Str::slug($name, '_');
            $attributes = [
                'customer_id'      => $customer->id,
                'name'             => $slug,
                'measurement_type' => MeasurementFields::GROUP_CUSTOM,
            ];

            $data = [
                'value'  => $entry['value'],
                'unit'   => $unit,
                'gender' => $gender,
                'notes'  => $name,
            ];

            if (! empty($entry['id'])) {
                $measurement = Measurement::where('id', $entry['id'])
                    ->where('customer_id', $customer->id)
                    ->where('measurement_type', MeasurementFields::GROUP_CUSTOM)
                    ->first();

                if ($measurement) {
                    $measurement->update($data + ['name' => $slug, 'notes' => $name]);
                    $customIds[] = $measurement->id;

                    continue;
                }
            }

            $created = Measurement::updateOrCreate($attributes, $data + ['notes' => $name]);
            $customIds[] = $created->id;
        }

        Measurement::query()
            ->where('customer_id', $customer->id)
            ->where('measurement_type', MeasurementFields::GROUP_CUSTOM)
            ->when(! empty($customIds), fn ($query) => $query->whereNotIn('id', $customIds))
            ->delete();

        $values = [];

        foreach ($this->indexedMeasurements($customer) as $measurement) {
            if ($measurement->measurement_type === MeasurementFields::GROUP_CUSTOM) {
                continue;
            }

            $values[$measurement->name] = [
                'value' => $measurement->value,
                'group' => $measurement->measurement_type,
            ];
        }

        return [
            'gender'       => $gender,
            'unit'         => $unit,
            'completeness' => $this->completeness($gender, $values),
            'measurements' => $customer->measurements()->get(),
        ];
    }

    /**
     * Calculate completeness for a gender + values map.
     */
    public function completeness(string $gender, array $values): array
    {
        $fields = MeasurementFields::fieldsForGender($gender);
        $total = count($fields);
        $filled = 0;
        $missing = [];

        foreach ($fields as $slug => $field) {
            $value = data_get($values, "{$slug}.value", data_get($values, $slug));

            if (is_array($value)) {
                $value = $value['value'] ?? null;
            }

            if ($value !== null && $value !== '' && (float) $value > 0) {
                $filled++;
            } else {
                $missing[] = $field['label'];
            }
        }

        return [
            'total'   => $total,
            'filled'  => $filled,
            'missing' => $missing,
            'percent' => $total > 0 ? (int) round(($filled / $total) * 100) : 0,
            'isComplete' => $filled === $total && $total > 0,
        ];
    }

    /**
     * Whether customer has any meaningful measurements saved.
     */
    public function hasMeasurements(Customer $customer): bool
    {
        return $customer->measurements()
            ->whereNotNull('value')
            ->where('value', '>', 0)
            ->exists();
    }

    protected function indexedMeasurements(Customer $customer)
    {
        return $customer->measurements()
            ->get()
            ->keyBy(fn (Measurement $measurement) => $measurement->measurement_type.'.'.$measurement->name);
    }
}
