@php
    use Webkul\Customer\Data\MeasurementFields;

    $measurementOptions = MeasurementFields::labelMap();
    $selectedMeasurements = old('required_measurement', $product->required_measurement ?? []);

    if (! is_array($selectedMeasurements)) {
        $selectedMeasurements = json_decode((string) $selectedMeasurements, true) ?? explode(',', (string) $selectedMeasurements);
    }
@endphp

<x-admin::form.control-group.control
    type="multiselect"
    id="required_measurement"
    name="required_measurement[]"
    :value="$selectedMeasurements"
    label="Required Measurement"
    multiple
>
    @foreach ($measurementOptions as $key => $label)
        <option
            value="{{ $key }}"
            {{ in_array($key, $selectedMeasurements) ? 'selected' : '' }}
        >
            {{ $label }}
        </option>
    @endforeach
</x-admin::form.control-group.control>
