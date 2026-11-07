<?php

namespace Webkul\Customer\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Webkul\Customer\Contracts\Customer;
use Webkul\Customer\Data\MeasurementFields;
use Webkul\Customer\Models\Measurement;
use Webkul\Customer\Models\MeasurementProfile;

class MeasurementService
{
    /**
     * Get all measurement profiles for a customer, default first.
     */
    public function profiles(Customer $customer): Collection
    {
        return MeasurementProfile::query()
            ->where('customer_id', $customer->id)
            ->orderByDesc('is_default')
            ->orderBy('id')
            ->get();
    }

    /**
     * Resolve a profile by id (must belong to the customer), falling back to
     * the default profile, then to the first profile.
     */
    public function resolveProfile(Customer $customer, ?int $profileId = null): ?MeasurementProfile
    {
        if ($profileId) {
            $profile = MeasurementProfile::query()
                ->where('id', $profileId)
                ->where('customer_id', $customer->id)
                ->first();

            if ($profile) {
                return $profile;
            }
        }

        return MeasurementProfile::query()
            ->where('customer_id', $customer->id)
            ->orderByDesc('is_default')
            ->orderBy('id')
            ->first();
    }

    /**
     * Create a measurement profile for a customer.
     */
    public function createProfile(Customer $customer, array $attributes): MeasurementProfile
    {
        $isFirst = ! MeasurementProfile::where('customer_id', $customer->id)->exists();

        $profile = MeasurementProfile::create([
            'customer_id'     => $customer->id,
            'name'            => $this->sanitizeProfileName($attributes['name'] ?? null),
            'gender'          => MeasurementFields::resolveGender($attributes['gender'] ?? $customer->gender),
            'unit'            => MeasurementFields::normalizeUnit($attributes['unit'] ?? null),
            'fit_preference'  => $this->sanitizeFitPreference($attributes['fit_preference'] ?? null),
            'fit_notes'       => $this->sanitizeFitNotes($attributes['fit_notes'] ?? null),
            'fit_notes_other' => $this->sanitizeFitNotesOther($attributes),
            'is_default'      => $isFirst,
        ]);

        if (! empty($attributes['is_default'])) {
            $this->markAsDefault($customer, $profile);
        }

        return $profile;
    }

    /**
     * Update a measurement profile.
     */
    public function updateProfile(Customer $customer, MeasurementProfile $profile, array $attributes): MeasurementProfile
    {
        $data = [];

        if (array_key_exists('name', $attributes) && trim((string) $attributes['name']) !== '') {
            $data['name'] = $this->sanitizeProfileName($attributes['name']);
        }

        if (! empty($attributes['gender'])) {
            $data['gender'] = MeasurementFields::resolveGender($attributes['gender']);
        }

        if (! empty($attributes['unit'])) {
            $data['unit'] = MeasurementFields::normalizeUnit($attributes['unit']);
        }

        if (array_key_exists('fit_preference', $attributes)) {
            $data['fit_preference'] = $this->sanitizeFitPreference($attributes['fit_preference']);
        }

        if (array_key_exists('fit_notes', $attributes)) {
            $data['fit_notes'] = $this->sanitizeFitNotes($attributes['fit_notes']);
        }

        if (array_key_exists('fit_notes_other', $attributes)) {
            $data['fit_notes_other'] = $this->sanitizeFitNotesOther($attributes);
        }

        if ($data) {
            $profile->update($data);
        }

        if (! empty($attributes['is_default'])) {
            $this->markAsDefault($customer, $profile);
        }

        return $profile->refresh();
    }

    /**
     * Delete a profile. Its measurements are removed by the FK cascade and the
     * default flag is reassigned to another profile when needed.
     */
    public function deleteProfile(Customer $customer, MeasurementProfile $profile): void
    {
        $wasDefault = $profile->is_default;

        $profile->delete();

        if ($wasDefault) {
            $next = MeasurementProfile::query()
                ->where('customer_id', $customer->id)
                ->orderBy('id')
                ->first();

            $next?->update(['is_default' => true]);
        }
    }

    /**
     * Make the given profile the customer's only default.
     */
    public function markAsDefault(Customer $customer, MeasurementProfile $profile): void
    {
        MeasurementProfile::query()
            ->where('customer_id', $customer->id)
            ->where('id', '!=', $profile->id)
            ->update(['is_default' => false]);

        if (! $profile->is_default) {
            $profile->update(['is_default' => true]);
        }
    }

    /**
     * Lightweight profile summaries for switchers and checkout dropdowns.
     */
    public function profileSummaries(Customer $customer): array
    {
        return $this->profiles($customer)
            ->map(fn (MeasurementProfile $profile) => $this->summarizeProfile($profile))
            ->values()
            ->all();
    }

    /**
     * Summarize a single profile including completeness.
     */
    public function summarizeProfile(MeasurementProfile $profile): array
    {
        return [
            'id'              => $profile->id,
            'name'            => $profile->name,
            'gender'          => $profile->gender,
            'unit'            => $profile->unit,
            'fit_preference'  => $profile->fit_preference,
            'fit_notes'       => $profile->fit_notes ?: [],
            'fit_notes_other' => $profile->fit_notes_other,
            'is_default'      => (bool) $profile->is_default,
            'completeness'    => $this->profileCompleteness($profile),
        ];
    }

    /**
     * Completeness for a profile's saved measurements.
     */
    public function profileCompleteness(MeasurementProfile $profile): array
    {
        $values = [];

        foreach ($profile->measurements as $measurement) {
            if ($measurement->measurement_type === MeasurementFields::GROUP_CUSTOM) {
                continue;
            }

            $values[$measurement->name] = [
                'value' => $measurement->value,
                'group' => $measurement->measurement_type,
            ];
        }

        return $this->completeness($profile->gender, $values);
    }

    /**
     * Build payload for the measurement form / API.
     */
    public function buildFormPayload(Customer $customer, ?MeasurementProfile $profile = null): array
    {
        $profile = $profile ?: $this->resolveProfile($customer);

        $profileGender = MeasurementFields::resolveGender($customer->gender);

        $values = [];
        $custom = [];

        if ($profile) {
            foreach ($profile->measurements as $measurement) {
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
        }

        $activeGender = $profile?->gender ?: $profileGender;
        $unit = $profile?->unit ?: MeasurementFields::UNIT_INCHES;

        return [
            'gender'        => $activeGender,
            'profileGender' => $profileGender,
            'unit'          => $unit,
            'values'        => $values,
            'custom'        => $custom,
            'fields'        => MeasurementFields::definitions(),
            'groupLabels'   => MeasurementFields::groupLabels(),
            'completeness'  => $this->completeness($activeGender, $values),
            'profile'       => $profile ? $this->summarizeProfile($profile) : null,
            'profiles'      => $this->profileSummaries($customer),
            'fitPreferences'  => MeasurementFields::fitPreferenceOptions(),
            'fitNoteOptions'  => MeasurementFields::fitNoteOptions(),
        ];
    }

    /**
     * Persist measurements from form/API payload.
     *
     * The payload may carry a `profile_id` to target an existing profile or a
     * `profile_name` to create a new one. Falls back to the default profile
     * (created on the fly for first-time customers).
     */
    public function save(Customer $customer, array $payload): array
    {
        $profile = $this->resolveTargetProfile($customer, $payload);

        $this->updateProfile($customer, $profile, [
            'name'            => $payload['profile_name'] ?? null,
            'gender'          => $payload['gender'] ?? null,
            'unit'            => $payload['unit'] ?? null,
            'fit_preference'  => $payload['fit_preference'] ?? null,
            'fit_notes'       => $payload['fit_notes'] ?? null,
            'fit_notes_other' => $payload['fit_notes_other'] ?? null,
            'is_default'      => $payload['is_default'] ?? null,
        ]);

        $profile->refresh();

        $gender = $profile->gender;
        $unit = $profile->unit;

        $measurements = $payload['measurements'] ?? [];
        $custom = $payload['custom'] ?? [];
        $allowedFields = MeasurementFields::fieldsForGender($gender);

        foreach ($allowedFields as $slug => $field) {
            $value = data_get($measurements, "{$field['group']}.{$slug}.value")
                ?? data_get($measurements, "{$field['group']}.{$slug}");

            if ($value === null || $value === '') {
                continue;
            }

            Measurement::updateOrCreate(
                [
                    'profile_id'       => $profile->id,
                    'name'             => $slug,
                    'measurement_type' => $field['group'],
                ],
                [
                    'customer_id' => $customer->id,
                    'value'       => $value,
                    'unit'        => $unit,
                    'gender'      => $gender,
                    'notes'       => null,
                ]
            );
        }

        $customIds = [];

        foreach ($custom as $entry) {
            $name = trim((string) ($entry['name'] ?? ''));

            if ($name === '' || ! isset($entry['value']) || $entry['value'] === '') {
                continue;
            }

            $slug = Str::slug($name, '_');
            $attributes = [
                'profile_id'       => $profile->id,
                'name'             => $slug,
                'measurement_type' => MeasurementFields::GROUP_CUSTOM,
            ];

            $data = [
                'customer_id' => $customer->id,
                'value'       => $entry['value'],
                'unit'        => $unit,
                'gender'      => $gender,
                'notes'       => $name,
            ];

            if (! empty($entry['id'])) {
                $measurement = Measurement::where('id', $entry['id'])
                    ->where('profile_id', $profile->id)
                    ->where('measurement_type', MeasurementFields::GROUP_CUSTOM)
                    ->first();

                if ($measurement) {
                    $measurement->update($data + ['name' => $slug]);
                    $customIds[] = $measurement->id;

                    continue;
                }
            }

            $created = Measurement::updateOrCreate($attributes, $data);
            $customIds[] = $created->id;
        }

        Measurement::query()
            ->where('profile_id', $profile->id)
            ->where('measurement_type', MeasurementFields::GROUP_CUSTOM)
            ->when(! empty($customIds), fn ($query) => $query->whereNotIn('id', $customIds))
            ->delete();

        $profile->load('measurements');

        $values = [];

        foreach ($profile->measurements as $measurement) {
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
            'measurements' => $profile->measurements,
            'profile'      => $this->summarizeProfile($profile),
            'profiles'     => $this->profileSummaries($customer),
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

    /**
     * Resolve the profile a save() call should write to.
     */
    protected function resolveTargetProfile(Customer $customer, array $payload): MeasurementProfile
    {
        if (! empty($payload['profile_id'])) {
            $profile = MeasurementProfile::query()
                ->where('id', (int) $payload['profile_id'])
                ->where('customer_id', $customer->id)
                ->first();

            if ($profile) {
                return $profile;
            }
        }

        $attributes = [
            'name'            => $payload['profile_name'] ?? null,
            'gender'          => $payload['gender'] ?? null,
            'unit'            => $payload['unit'] ?? null,
            'fit_preference'  => $payload['fit_preference'] ?? null,
            'fit_notes'       => $payload['fit_notes'] ?? null,
            'fit_notes_other' => $payload['fit_notes_other'] ?? null,
        ];

        // Explicit new-profile request from the form.
        if (! empty($payload['create_profile'])) {
            return $this->createProfile($customer, $attributes);
        }

        // Fall back to the default/first profile, creating one if none exists.
        return $this->resolveProfile($customer) ?: $this->createProfile($customer, $attributes);
    }

    /**
     * Fallback-safe profile name.
     */
    protected function sanitizeProfileName($name): string
    {
        $name = trim((string) $name);

        return $name !== '' ? Str::limit($name, 100, '') : 'My Measurements';
    }

    /**
     * Keep only known fit preference values.
     */
    protected function sanitizeFitPreference($value): ?string
    {
        return array_key_exists((string) $value, MeasurementFields::fitPreferenceOptions())
            ? (string) $value
            : null;
    }

    /**
     * Keep only known fit note slugs.
     */
    protected function sanitizeFitNotes($notes): ?array
    {
        if (! is_array($notes)) {
            return null;
        }

        $allowed = array_keys(MeasurementFields::fitNoteOptions());

        $notes = array_values(array_unique(array_filter($notes, fn ($note) => in_array($note, $allowed, true))));

        return $notes ?: null;
    }

    /**
     * Only keep the "other" free text when the "other" note is selected.
     */
    protected function sanitizeFitNotesOther(array $attributes): ?string
    {
        $notes = $this->sanitizeFitNotes($attributes['fit_notes'] ?? null) ?: [];

        if (! in_array(MeasurementFields::FIT_NOTE_OTHER, $notes, true)) {
            return null;
        }

        $other = trim((string) ($attributes['fit_notes_other'] ?? ''));

        return $other !== '' ? Str::limit($other, 500, '') : null;
    }
}
