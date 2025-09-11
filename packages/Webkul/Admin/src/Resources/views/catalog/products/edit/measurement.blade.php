@php
    // Example measurement options, replace with your actual source if needed
    $measurementOptions = [
        'length' => 'Length',
        'width' => 'Width',
        'height' => 'Height',
        'chest' => 'Chest',
        'waist' => 'Waist',
        'sleeve' => 'Sleeve',
    ];

    $selectedMeasurements = old('required_measurement', $product->required_measurement ?? []);
    if (!is_array($selectedMeasurements)) {
        $selectedMeasurements = explode(',', $selectedMeasurements);
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
