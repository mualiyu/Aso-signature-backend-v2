@php
    $inputClass = 'w-full rounded-lg border px-5 py-3 text-base font-normal text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 max-sm:px-4 max-md:py-2 max-sm:text-sm';
@endphp

<x-shop::layouts.account>
    <x-slot:title>
        @lang('alteration::app.shop.claims.create-title', ['order' => $order->increment_id])
    </x-slot>

    <div class="max-md:hidden">
        <x-shop::layouts.account.navigation />
    </div>

    <div class="mx-4 min-w-0 flex-auto max-md:mx-6 max-sm:mx-4">
        <div class="mb-8 flex items-center max-sm:mb-5">
            <a class="grid md:hidden" href="{{ route('shop.customers.account.orders.view', $order->id) }}">
                <span class="icon-arrow-left rtl:icon-arrow-right text-2xl"></span>
            </a>

            <h2 class="text-2xl font-medium max-md:text-xl max-sm:text-base ltr:ml-2.5 md:ltr:ml-0 rtl:mr-2.5 md:rtl:mr-0">
                @lang('alteration::app.shop.claims.create-title', ['order' => $order->increment_id])
            </h2>
        </div>

        <p class="mb-2 text-base text-zinc-500 max-sm:text-sm">@lang('alteration::app.shop.claims.intro')</p>

        <p class="mb-8 text-base font-medium max-sm:mb-5 max-sm:text-sm">@lang('alteration::app.shop.claims.deadline', ['date' => core()->formatDate($deadline, 'd M Y')])</p>

        @if ($errors->any())
            <div class="mb-6 rounded-xl border border-red-500 px-5 py-4 text-sm text-red-500">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form
            method="POST"
            action="{{ route('shop.customers.account.alterations.store', $order->id) }}"
            enctype="multipart/form-data"
        >
            @csrf

            <!-- Reason -->
            <div class="mb-8">
                <p class="mb-3 text-lg font-medium max-sm:text-base">@lang('alteration::app.shop.claims.reason')</p>

                <div class="flex flex-col gap-3">
                    @foreach (config('alteration.claim_reasons') as $reason)
                        <label class="flex cursor-pointer items-center gap-2.5 text-base max-sm:text-sm">
                            <input type="radio" name="reason" value="{{ $reason }}" required @checked(old('reason', 'measurement_mismatch') === $reason)>

                            <span>@lang('alteration::app.admin.claims.reasons.'.$reason)</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Measurement comparison -->
            <div class="mb-8">
                <p class="mb-1 text-lg font-medium max-sm:text-base">@lang('alteration::app.shop.claims.comparison')</p>

                <p class="mb-4 text-sm text-zinc-500">@lang('alteration::app.shop.claims.comparison-info')</p>

                @foreach ($measurementsByItem as $itemId => $measurements)
                    <div class="mb-5 rounded-xl border border-zinc-200">
                        <p class="border-b border-zinc-200 px-5 py-3 text-base font-medium max-sm:px-4 max-sm:text-sm" v-pre>
                            {{ $items->get($itemId)?->name ?? __('alteration::app.shop.claims.order').' #'.$order->increment_id }}

                            @if ($measurements->first()->profile_name)
                                <span class="text-zinc-500">({{ $measurements->first()->profile_name }})</span>
                            @endif
                        </p>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead>
                                    <tr class="border-b border-zinc-200">
                                        <th class="px-5 py-3 font-medium max-sm:px-4">@lang('alteration::app.shop.claims.measurement')</th>
                                        <th class="px-5 py-3 font-medium max-sm:px-4">@lang('alteration::app.shop.claims.confirmed')</th>
                                        <th class="px-5 py-3 font-medium max-sm:px-4">@lang('alteration::app.shop.claims.garment')</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach ($measurements as $measurement)
                                        <tr class="border-b border-zinc-200">
                                            <td class="px-5 py-3 max-sm:px-4">{{ $labels[$measurement->name] ?? \Illuminate\Support\Str::headline($measurement->name) }}</td>

                                            <td class="px-5 py-3 max-sm:px-4">{{ rtrim(rtrim(number_format((float) $measurement->value, 2), '0'), '.') }} {{ $measurement->unit }}</td>

                                            <td class="px-5 py-3 max-sm:px-4">
                                                <input
                                                    type="number"
                                                    step="0.1"
                                                    min="0"
                                                    name="garment[{{ $measurement->id }}]"
                                                    value="{{ old('garment.'.$measurement->id) }}"
                                                    class="w-full max-w-[120px] rounded-lg border px-3 py-2 text-sm text-gray-600"
                                                    aria-label="{{ $labels[$measurement->name] ?? $measurement->name }}"
                                                >
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Photos -->
            <div class="mb-8">
                <p class="mb-1 text-lg font-medium max-sm:text-base">@lang('alteration::app.shop.claims.photos')</p>

                <p class="mb-4 text-sm text-zinc-500">@lang('alteration::app.shop.claims.photos-info', ['min' => config('alteration.min_photos')])</p>

                <input type="file" name="photos[]" accept="image/*" multiple required class="{{ $inputClass }}">
            </div>

            <!-- Description -->
            <div class="mb-8">
                <p class="mb-3 text-lg font-medium max-sm:text-base">@lang('alteration::app.shop.claims.description')</p>

                <textarea name="description" rows="5" maxlength="2000" required class="{{ $inputClass }}">{{ old('description') }}</textarea>
            </div>

            <div class="flex flex-wrap items-center gap-4">
                <button type="submit" class="primary-button m-0 block rounded-2xl px-11 py-3 text-center text-base max-md:w-full max-md:max-w-full max-md:rounded-lg max-md:py-1.5">
                    @lang('alteration::app.shop.claims.submit-btn')
                </button>

                <a href="{{ route('shop.customers.account.orders.view', $order->id) }}" class="secondary-button border-zinc-200 px-5 py-3 font-normal max-md:w-full">
                    @lang('alteration::app.shop.claims.back-btn')
                </a>
            </div>
        </form>
    </div>
</x-shop::layouts.account>
