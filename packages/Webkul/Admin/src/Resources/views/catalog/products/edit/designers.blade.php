@php
    use Webkul\Designer\Models\Designer;

    $selectedDesigner = old('designer_id') ?: ($product->designer_id ?? null);
    $designers = Designer::orderBy('name')->get();
@endphp

<x-admin::form.control-group.control
    type="select2"
    id="designer_id"
    name="designer_id"
    :value="$selectedDesigner"
    label="Designer"
>
    <option value="">Select Designer</option>
    @foreach ($designers as $designer)
        <option
            value="{{ $designer->id }}"
            {{ $selectedDesigner == $designer->id ? 'selected' : '' }}
        >
            {{ $designer->name }}
        </option>
    @endforeach
</x-admin::form.control-group.control>
