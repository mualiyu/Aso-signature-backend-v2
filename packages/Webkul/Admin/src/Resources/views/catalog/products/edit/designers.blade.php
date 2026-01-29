@php
use Webkul\Designer\Models\Designer;

$selectedDesigner = old('designer_id') ?: ($product->designer_id ?? null);
$designers = Designer::orderBy('name')->get();
@endphp

<div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
    <!-- Panel Header -->
    <p class="mb-4 flex justify-between text-base font-semibold text-gray-800 dark:text-white">
        Select Designer
    </p>

    <!-- Panel Content -->
    <div class="mb-5 text-sm text-gray-600 dark:text-gray-300">

        <x-admin::form.control-group.control type="select2" id="designer_id" name="designer_id"
            :value="$selectedDesigner" label="Designer">
            <option value="">Select Designer</option>
            @foreach ($designers as $designer)
            <option value="{{ $designer->id }}" {{ $selectedDesigner==$designer->id ? 'selected' : '' }}
                >
                {{ $designer->name }}
            </option>
            @endforeach
        </x-admin::form.control-group.control>


    </div>

</div>
