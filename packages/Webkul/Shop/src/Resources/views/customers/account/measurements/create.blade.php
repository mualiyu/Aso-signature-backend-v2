<x-shop::layouts.account>
    <!-- Page Title -->
    <x-slot:title>
        Update Measurements
    </x-slot>

    <!-- Breadcrumbs -->
    {{-- @if ((core()->getConfigData('general.general.breadcrumbs.shop')))
        @section('breadcrumbs')
            <x-shop::breadcrumbs name="measurements.create" />
        @endSection
    @endif --}}

    <div class="max-md:hidden">
        <x-shop::layouts.account.navigation />
    </div>

    <div class="mx-4 flex-auto max-md:mx-6 max-sm:mx-4">
        <div class="mb-2 flex items-center max-md:mb-5">
            <!-- Back Button -->
            <a
                class="grid md:hidden"
                href="{{ route('shop.customers.account.measurements.index') }}"
            >
                <span class="icon-arrow-left rtl:icon-arrow-right text-2xl"></span>
            </a>

            <h2 class="text-2xl font-medium max-md:text-xl max-sm:text-base ltr:ml-2.5 md:ltr:ml-0 rtl:mr-2.5 md:rtl:mr-0">
                Measurements
            </h2>
        </div>



        <x-shop::form :action="route('shop.customers.account.measurements.store')">
            @method('POST')
            @csrf
            <input type="hidden" name="customer_id" value="{{ auth()->guard('customer')->user()->id }}">

            {{-- units --}}
                <div class="mb-4 flex items-center justify-end">
                    <select name="unit" id="unit" class="form-input border border-gray-300 rounded px-2 py-1 w-auto" required>
                        <option value="CM" {{ \Webkul\Customer\Models\Measurement::where('customer_id', auth()->guard('customer')->user()->id)->first()?->unit == 'CM' ? 'selected' : '' }}>CM</option>
                        <option value="Inches" {{ \Webkul\Customer\Models\Measurement::where('customer_id', auth()->guard('customer')->user()->id)->first()?->unit == 'Inches' ? 'selected' : '' }}>Inches</option>
                    </select>
                    {{-- <span class="ml-2 font-medium">Measurement Unit</span> --}}
                </div>

            <!-- TOP MEASUREMENTS -->
            <div class="mb-8">
            <button type="button" class="flex items-center justify-between w-full mb-2 text-lg font-semibold focus:outline-none" onclick="toggleSection('top-section')">
                <span>TOP</span>
                <span class="ml-2 transition-transform" id="icon-top-section">&#9660;</span>
            </button>
            <hr class="mb-4 border-gray-300">
            <div id="top-section" class="flex flex-wrap gap-4">
                @foreach([
                'Neck', 'Chest', 'Shoulder', 'Off shoulder', 'Upper bust', 'Bust', 'Bust Point', 'Round under bust',
                'Shoulder to under bust', 'Waist', 'Half Length', 'Back Half Length', 'Sleeve length', 'Round sleeve', 'Top length'
                ] as $field)
                <div class="flex-1 min-w-[250px] max-w-[33%]">
                    <x-shop::form.control-group>
                    <x-shop::form.control-group.label>
                        {{ $field }}
                    </x-shop::form.control-group.label>
                    <div class="flex gap-2">
                        <x-shop::form.control-group.control
                        type="number"
                        step="0.1"
                        min="0"
                        name="measurements[top][{{ \Str::slug($field, '_') }}][value]"
                        :label="$field"
                        :placeholder="$field"
                        :value="\Webkul\Customer\Models\Measurement::where(['customer_id'=> auth()->guard('customer')->user()->id, 'measurement_type'=> 'top', 'name'=>\Str::slug($field, '_')])->first()?->value ?? null"
                        />
                    </div>
                    </x-shop::form.control-group>
                </div>
                @endforeach
            </div>
            </div>

            <!-- SKIRT MEASUREMENTS -->
            <div class="mb-8">
            <button type="button" class="flex items-center justify-between w-full mb-2 text-lg font-semibold focus:outline-none" onclick="toggleSection('skirt-section')">
                <span>SKIRT</span>
                <span class="ml-2 transition-transform" id="icon-skirt-section">&#9660;</span>
            </button>
             <hr class="mb-4 border-gray-300">
            <div id="skirt-section" class="flex flex-wrap gap-4">
                @foreach([
                'Hip', 'Waist to knee', 'Waist to below knee(Mid length)', 'Waist to Ankle (maxi Length)'
                ] as $field)
                <div class="flex-1 min-w-[250px] max-w-[33%]">
                    <x-shop::form.control-group>
                    <x-shop::form.control-group.label>
                        {{ $field }}
                    </x-shop::form.control-group.label>
                    <div class="flex gap-2">
                        <x-shop::form.control-group.control
                        type="number"
                        step="0.1"
                        min="0"
                        name="measurements[skirt][{{ \Str::slug($field, '_') }}][value]"
                        :label="$field"
                        :placeholder="$field"
                        :value="\Webkul\Customer\Models\Measurement::where(['customer_id'=> auth()->guard('customer')->user()->id, 'measurement_type'=> 'skirt', 'name'=>\Str::slug($field, '_')])->first()?->value ?? null"
                        />
                    </div>
                    </x-shop::form.control-group>
                </div>
                @endforeach
            </div>
            </div>

            <!-- DRESS MEASUREMENTS -->
            <div class="mb-8">
            <button type="button" class="flex items-center justify-between w-full mb-2 text-lg font-semibold focus:outline-none" onclick="toggleSection('dress-section')">
                <span>DRESS</span>
                <span class="ml-2 transition-transform" id="icon-dress-section">&#9660;</span>
            </button>
             <hr class="mb-4 border-gray-300">
            <div id="dress-section" class="flex flex-wrap gap-4">
                @foreach([
                'Full length', 'Mid length', 'Knee length', 'Short length'
                ] as $field)
                <div class="flex-1 min-w-[250px] max-w-[33%]">
                    <x-shop::form.control-group>
                    <x-shop::form.control-group.label>
                        {{ $field }}
                    </x-shop::form.control-group.label>
                    <div class="flex gap-2">
                        <x-shop::form.control-group.control
                        type="number"
                        step="0.1"
                        min="0"
                        name="measurements[dress][{{ \Str::slug($field, '_') }}][value]"
                        :label="$field"
                        :placeholder="$field"
                        :value="\Webkul\Customer\Models\Measurement::where(['customer_id'=> auth()->guard('customer')->user()->id, 'measurement_type'=> 'dress', 'name'=>\Str::slug($field, '_')])->first()?->value ?? null"
                        />
                    </div>
                    </x-shop::form.control-group>
                </div>
                @endforeach
            </div>
            </div>

            <!-- TROUSER MEASUREMENTS -->
            <div class="mb-8">
            <button type="button" class="flex items-center justify-between w-full mb-2 text-lg font-semibold focus:outline-none" onclick="toggleSection('trouser-section')">
                <span>TROUSER</span>
                <span class="ml-2 transition-transform" id="icon-trouser-section">&#9660;</span>
            </button>
             <hr class="mb-4 border-gray-300">
            <div id="trouser-section" class="flex flex-wrap gap-4">
                @foreach([
                'Waist', 'Crotch', 'Round knee', 'In seam', 'Out seam', 'Thighs/laps', 'Palazzo thigh', 'Full Trouser length'
                ] as $field)
                <div class="flex-1 min-w-[250px] max-w-[33%]">
                    <x-shop::form.control-group>
                    <x-shop::form.control-group.label>
                        {{ $field }}
                    </x-shop::form.control-group.label>
                    <div class="flex gap-2">
                        <x-shop::form.control-group.control
                        type="number"
                        step="0.1"
                        min="0"
                        name="measurements[trouser][{{ \Str::slug($field, '_') }}][value]"
                        :label="$field"
                        :placeholder="$field"
                        :value="\Webkul\Customer\Models\Measurement::where(['customer_id'=> auth()->guard('customer')->user()->id, 'measurement_type'=> 'trouser', 'name'=>\Str::slug($field, '_')])->first()?->value ?? null"
                        />
                    </div>
                    </x-shop::form.control-group>
                </div>
                @endforeach
            </div>
            </div>

            <!-- Custom Measurements -->
            <div class="mb-8" id="custom-measurements-section">
            <button type="button" class="flex items-center justify-between w-full mb-2 text-lg font-semibold focus:outline-none" onclick="toggleSection('custom-measurements-list-1')">
                <span>Custom Measurements</span>
                <span class="ml-2 transition-transform" id="icon-custom-measurements-list-1">&#9660;</span>
            </button>
             <hr class="mb-4 border-gray-300">
             <div id="custom-measurements-list-1">
                <div id="custom-measurements-list" class="flex flex-wrap gap-4">
                @foreach(\Webkul\Customer\Models\Measurement::where(['customer_id'=> auth()->guard('customer')->user()->id, 'measurement_type'=> 'custom'])->get() ?: [] as $measurement)
                    <div class="flex gap-2 mb-2" id="custom-measurement-{{ $measurement->id }}">
                        <x-shop::form.control-group>
                            <x-shop::form.control-group.label>
                                <input type="text" name="measurements[custom][{{ $measurement->id }}][name]" value="{{ $measurement->name }}" placeholder="Measurement Name" class="form-input border border-gray-300 rounded px-2 py-1" required>
                            </x-shop::form.control-group.label>
                            <div class="flex gap-2">
                                <x-shop::form.control-group.control
                                    type="number"
                                    step="0.1"
                                    min="0"
                                    name="measurements[custom][{{ $measurement->id }}][value]"
                                    :label="'Value'"
                                    placeholder="Value"
                                    value="{{ $measurement->value }}"
                                    required
                                />
                            </div>
                        </x-shop::form.control-group>
                        <button type="button" onclick="removeCustomMeasurement({{ $measurement->id }})" class="text-red-500 font-bold mt-5">X</button>
                    </div>
                @endforeach
                </div>
                <button type="button" class="secondary-button" onclick="addCustomMeasurement()">Add Custom Measurement</button><br>
             </div>
            </div>

            @php
                $redirect = request()->query('redirect');
            @endphp

            @if ($redirect)
                <input type="hidden" name="redirect" value="{{ $redirect }}">
                <button
                    type="submit"
                    class="primary-button m-0 block rounded-2xl px-11 py-3 text-center text-base max-md:w-full max-md:max-w-full max-md:rounded-lg max-md:py-2 max-sm:py-1.5"
                >
                    Save Measurement and Continue to Checkout
                </button>
            @else
            <button
            type="submit"
            class="primary-button m-0 block rounded-2xl px-11 py-3 text-center text-base max-md:w-full max-md:max-w-full max-md:rounded-lg max-md:py-2 max-sm:py-1.5"
            >
            Save Measurements
            </button>
            @endif
        </x-shop::form>

        <script>
            function toggleSection(sectionId) {
            const section = document.getElementById(sectionId);
            const icon = document.getElementById('icon-' + sectionId);
            if (section.style.display === 'none') {
                section.style.display = '';
                icon.style.transform = 'rotate(0deg)';
            } else {
                section.style.display = 'none';
                icon.style.transform = 'rotate(-90deg)';
            }
            }

            // Optionally, collapse all except the first by default
            document.addEventListener('DOMContentLoaded', function () {
            // List of section IDs
            const sections = [
                'top-section',
                'skirt-section',
                'dress-section',
                'trouser-section',
                'custom-measurements-list-1'
            ];
            // Open the first, collapse the rest
            for (let i = 1; i < sections.length; i++) {
                document.getElementById(sections[i]).style.display = 'none';
                document.getElementById('icon-' + sections[i]).style.transform = 'rotate(-90deg)';
            }
            });
        </script>
    </div>

    <script>
        function addCustomMeasurement() {
            const list = document.getElementById('custom-measurements-list');
            const index = list.children.length;
            const div = document.createElement('div');
            div.className = 'flex gap-2 mb-2';
            div.innerHTML = `
                <x-shop::form.control-group>
                    <x-shop::form.control-group.label>
                        <input type="text" name="measurements[custom][${index}][name]" placeholder="Measurement Name" class="form-input border border-gray-300 rounded px-2 py-1" required>
                    </x-shop::form.control-group.label>
                    <div class="flex gap-2">
                        <x-shop::form.control-group.control
                            type="number"
                            step="0.1"
                            min="0"
                            name="measurements[custom][${index}][value]"
                            :label="'Value'"
                            placeholder="Value"
                            required
                        />
                    </div>
                </x-shop::form.control-group>

                <button type="button" onclick="this.parentNode.remove()" class="text-red-500 font-bold mt-5">X</button>
            `;
            list.appendChild(div);
        }
    </script>

    <script>
        function removeCustomMeasurement(id) {
            const measurement = document.getElementById('custom-measurement-' + id);
            if (measurement) {
                // Optionally, you can also send an AJAX request to delete the measurement from the database
                fetch(`/customer/account/measurements/delete/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(response => {
                    if (response.ok) {
                        measurement.remove();
                    } else { }
                })
                measurement.remove();
            }
        }
    </script>

    {{-- <select name="measurements[custom][${index}][unit]" class="form-input">
                    <option value="cm">cm</option>
                    <option value="inches">inches</option>
                </select> --}}
</x-shop::layouts.account>
