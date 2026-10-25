<x-admin::layouts>
    <x-slot:title>
        @lang('alteration::app.admin.fix-types.title')
    </x-slot>

    @php
        $baseCurrency = core()->getBaseCurrencyCode();

        $inputClass = 'rounded-md border px-2.5 py-1.5 text-sm text-gray-600 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300';
    @endphp

    <form method="POST" action="{{ route('admin.alterations.fix_types.update') }}">
        @csrf
        @method('PUT')

        <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
            <p class="text-xl font-bold text-gray-800 dark:text-white">
                @lang('alteration::app.admin.fix-types.title')
            </p>

            <button type="submit" class="primary-button">
                @lang('alteration::app.admin.fix-types.save-btn')
            </button>
        </div>

        <div class="box-shadow mt-3.5 rounded bg-white p-4 dark:bg-gray-900">
            <p class="mb-4 text-sm text-gray-600 dark:text-gray-300">
                @lang('alteration::app.admin.fix-types.info')
            </p>

            @if ($errors->any())
                <p class="mb-4 text-sm text-red-600">{{ $errors->first() }}</p>
            @endif

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
                    <thead>
                        <tr class="border-b dark:border-gray-800">
                            <th class="px-2 py-2 font-semibold">@lang('alteration::app.admin.fix-types.name')</th>
                            <th class="px-2 py-2 font-semibold">@lang('alteration::app.admin.fix-types.cap')</th>
                            <th class="px-2 py-2 font-semibold">@lang('alteration::app.admin.fix-types.currency')</th>
                            <th class="px-2 py-2 font-semibold">@lang('alteration::app.admin.fix-types.sort')</th>
                            <th class="px-2 py-2 font-semibold">@lang('alteration::app.admin.fix-types.active')</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($fixTypes as $fixType)
                            <tr class="border-b dark:border-gray-800">
                                <td class="px-2 py-2">
                                    <input type="text" name="fix_types[{{ $fixType->id }}][name]" value="{{ old('fix_types.'.$fixType->id.'.name', $fixType->name) }}" class="{{ $inputClass }} w-72" required>
                                </td>

                                <td class="px-2 py-2">
                                    <input type="number" step="0.01" min="0" name="fix_types[{{ $fixType->id }}][aso_cap_amount]" value="{{ old('fix_types.'.$fixType->id.'.aso_cap_amount', $fixType->aso_cap_amount !== null ? (float) $fixType->aso_cap_amount : '') }}" class="{{ $inputClass }} w-28">
                                </td>

                                <td class="px-2 py-2">
                                    <input type="text" maxlength="3" name="fix_types[{{ $fixType->id }}][currency]" value="{{ old('fix_types.'.$fixType->id.'.currency', $fixType->currency) }}" placeholder="{{ $baseCurrency }}" class="{{ $inputClass }} w-20 uppercase">
                                </td>

                                <td class="px-2 py-2">
                                    <input type="number" min="0" name="fix_types[{{ $fixType->id }}][sort]" value="{{ old('fix_types.'.$fixType->id.'.sort', $fixType->sort) }}" class="{{ $inputClass }} w-16">
                                </td>

                                <td class="px-2 py-2">
                                    <input type="hidden" name="fix_types[{{ $fixType->id }}][is_active]" value="0">

                                    <input type="checkbox" name="fix_types[{{ $fixType->id }}][is_active]" value="1" @checked($fixType->is_active)>
                                </td>
                            </tr>
                        @endforeach

                        <tr>
                            <td class="px-2 pb-2 pt-4" colspan="5">
                                <p class="font-semibold text-gray-800 dark:text-white">@lang('alteration::app.admin.fix-types.add-new')</p>
                            </td>
                        </tr>

                        <tr>
                            <td class="px-2 py-2">
                                <input type="text" name="new[name]" value="{{ old('new.name') }}" class="{{ $inputClass }} w-72">
                            </td>

                            <td class="px-2 py-2">
                                <input type="number" step="0.01" min="0" name="new[aso_cap_amount]" value="{{ old('new.aso_cap_amount') }}" class="{{ $inputClass }} w-28">
                            </td>

                            <td class="px-2 py-2">
                                <input type="text" maxlength="3" name="new[currency]" value="{{ old('new.currency') }}" placeholder="{{ $baseCurrency }}" class="{{ $inputClass }} w-20 uppercase">
                            </td>

                            <td colspan="2"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </form>
</x-admin::layouts>
