@php
use Webkul\Product\Models\Product;

$selectedGender = old('gender') ?: ($product->gender ?? null);
$genderOptions = Product::getGenderOptions();
@endphp

<div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
    <!-- Panel Header -->
    <p class="mb-4 flex justify-between text-base font-semibold text-gray-800 dark:text-white">
        Gender
    </p>

    <!-- Panel Content -->
    <div class="mb-5 text-sm text-gray-600 dark:text-gray-300">
        <x-admin::form.control-group.control
            type="select"
            id="gender"
            name="gender"
            :value="$selectedGender"
            label="Gender"
        >
            <option value="">Select Gender</option>
            @foreach ($genderOptions as $value => $label)
                <option value="{{ $value }}" {{ $selectedGender == $value ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </x-admin::form.control-group.control>
    </div>
</div>
