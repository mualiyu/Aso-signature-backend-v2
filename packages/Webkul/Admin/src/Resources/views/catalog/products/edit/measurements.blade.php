@php
    // Use null coalescing and type check to avoid deprecation warning
    $measurementsRaw = $product->required_measurement ?? null;
    $measurements = is_string($measurementsRaw) && trim($measurementsRaw) !== ''
        ? json_decode($measurementsRaw, true)
        : null;
    $measurements = $measurements ?? [
        'Neck', 'Chest', 'Shoulder', 'Off shoulder', 'Upper bust', 'Bust', 'Bust Point', 'Round under bust',
        'Shoulder to under bust', 'Waist', 'Half Length', 'Back Half Length', 'Sleeve length', 'Round sleeve', 'Top length',
        'Hip', 'Waist to knee', 'Waist to below knee(Mid length)', 'Waist to Ankle (maxi Length)', 'Full length', 'Mid length',
        'Knee length', 'Short length', 'Waist', 'Crotch', 'Round knee', 'In seam', 'Out seam', 'Thighs/laps', 'Palazzo thigh',
        'Full Trouser length'
    ];
    $selectedMeasurements = (isset($product->required_measurements) && is_array($product->required_measurements))
        ? array_map('strval', $product->required_measurements)
        : [];
@endphp

<div class="form-group">
    <label for="required_measurements">Required Measurements</label>
    <x-admin::form.control-group.control
        type="multiselect"
        id="required_measurements"
        name="required_measurements[]"
        :value="old('required_measurements', $selectedMeasurements)"
        multiple
        class="form-control"
        style="width: 100%;"
    >
        @foreach ($measurements as $measurement)
            <option
                value="{{ $measurement }}"
                @if(in_array($measurement, $selectedMeasurements)) selected @endif
            >
                {{ $measurement }}
            </option>
        @endforeach
    </x-admin::form.control-group.control>
</div>

<div class="form-group mt-4">
    <label for="custom_measurements">Add Custom Measurements</label>
    <div id="custom-measurements-list">
        @if(isset($product->custom_measurements) && is_array($product->custom_measurements))
            @foreach($product->custom_measurements as $custom)
                <div class="input-group mb-2">
                    <input type="text" name="custom_measurements[]" class="form-control" value="{{ $custom }}" placeholder="Custom Measurement">
                    <button type="button" class="btn btn-danger remove-custom-measurement">Remove</button>
                </div>
            @endforeach
        @else
            <div class="input-group mb-2">
                <input type="text" name="custom_measurements[]" class="form-control" placeholder="Custom Measurement">
                <button type="button" class="btn btn-danger remove-custom-measurement">Remove</button>
            </div>
        @endif
    </div>
    <button type="button" class="btn btn-primary mt-2" id="add-custom-measurement">Add Custom Measurement</button>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('add-custom-measurement').addEventListener('click', function () {
            let container = document.getElementById('custom-measurements-list');
            let div = document.createElement('div');
            div.className = 'input-group mb-2';
            div.innerHTML = `
                <input type="text" name="custom_measurements[]" class="form-control" placeholder="Custom Measurement">
                <button type="button" class="btn btn-danger remove-custom-measurement">Remove</button>
            `;
            container.appendChild(div);
        });

        document.getElementById('custom-measurements-list').addEventListener('click', function (e) {
            if (e.target.classList.contains('remove-custom-measurement')) {
                e.target.parentElement.remove();
            }
        });
    });
</script>
@endpush
