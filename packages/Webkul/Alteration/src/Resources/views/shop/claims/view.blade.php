@php
    $labels = \Webkul\Customer\Data\MeasurementFields::labelMap();

    $partner = $claim->partner;
@endphp

<x-shop::layouts.account>
    <x-slot:title>
        @lang('alteration::app.shop.claims.view-title', ['id' => $claim->id])
    </x-slot>

    <div class="max-md:hidden">
        <x-shop::layouts.account.navigation />
    </div>

    <div class="mx-4 min-w-0 flex-auto max-md:mx-6 max-sm:mx-4">
        <div class="mb-8 flex items-center max-sm:mb-5">
            <a class="grid md:hidden" href="{{ route('shop.customers.account.alterations.index') }}">
                <span class="icon-arrow-left rtl:icon-arrow-right text-2xl"></span>
            </a>

            <h2 class="text-2xl font-medium max-md:text-xl max-sm:text-base ltr:ml-2.5 md:ltr:ml-0 rtl:mr-2.5 md:rtl:mr-0">
                @lang('alteration::app.shop.claims.view-title', ['id' => $claim->id])
            </h2>
        </div>

        <div class="mb-8 grid grid-cols-3 gap-4 rounded-xl border border-zinc-200 px-5 py-4 max-md:grid-cols-1 max-sm:px-4">
            <div>
                <p class="text-sm text-zinc-500">@lang('alteration::app.shop.claims.order')</p>

                <a href="{{ route('shop.customers.account.orders.view', $claim->order_id) }}" class="text-base font-medium text-blue-700 underline">#{{ $claim->order->increment_id }}</a>
            </div>

            <div>
                <p class="text-sm text-zinc-500">@lang('alteration::app.shop.claims.status')</p>

                <p class="text-base font-medium">@lang('alteration::app.admin.claims.statuses.'.$claim->status)</p>
            </div>

            <div>
                <p class="text-sm text-zinc-500">@lang('alteration::app.shop.claims.submitted')</p>

                <p class="text-base font-medium">{{ core()->formatDate($claim->created_at, 'd M Y') }}</p>
            </div>
        </div>

        @if ($claim->customer_message)
            <div class="mb-8 rounded-xl border border-zinc-200 px-5 py-4 max-sm:px-4">
                <p class="mb-2 text-base font-medium">@lang('alteration::app.shop.claims.message-from-us')</p>

                <p class="text-base text-zinc-500" v-pre>{!! nl2br(e($claim->customer_message)) !!}</p>
            </div>
        @endif

        @if ($claim->status === 'partner_assigned' && $partner)
            <div class="mb-8 rounded-xl border border-zinc-200 px-5 py-4 max-sm:px-4" v-pre>
                <p class="mb-2 text-base font-medium">@lang('alteration::app.shop.claims.your-partner')</p>

                <p class="text-base">{{ $partner->name }} · {{ $partner->city }}</p>

                @if ($partner->address)
                    <p class="text-sm text-zinc-500">{{ $partner->address }}</p>
                @endif

                <p class="text-sm text-zinc-500">{{ collect([$partner->phone, $partner->whatsapp, $partner->email])->filter()->unique()->implode(' · ') }}</p>
            </div>
        @endif

        @if ($claim->status === 'customer_fault')
            <div class="mb-8">
                <p class="mb-4 text-base text-zinc-500">@lang('alteration::app.shop.claims.customer-fault-info')</p>

                @include('alteration::shop.locator.component', [
                    'defaultCountry' => $claim->order->shipping_address?->country,
                    'defaultCity'    => $claim->order->shipping_address?->city,
                ])
            </div>
        @endif

        @if ($claim->items->isNotEmpty())
            <p class="mb-3 text-lg font-medium max-sm:text-base">@lang('alteration::app.shop.claims.comparison')</p>

            <div class="mb-8 overflow-x-auto rounded-xl border border-zinc-200">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-zinc-200">
                            <th class="px-5 py-3 font-medium max-sm:px-2">@lang('alteration::app.shop.claims.measurement')</th>
                            <th class="px-5 py-3 font-medium max-sm:px-2">@lang('alteration::app.shop.claims.confirmed')</th>
                            <th class="px-5 py-3 font-medium max-sm:px-2">@lang('alteration::app.shop.claims.garment')</th>
                            <th class="px-5 py-3 font-medium max-sm:px-2">@lang('alteration::app.shop.claims.difference')</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($claim->items as $item)
                            <tr class="border-b border-zinc-200">
                                <td class="px-5 py-3 max-sm:px-2">{{ $labels[$item->measurement_name] ?? \Illuminate\Support\Str::headline($item->measurement_name) }}</td>
                                <td class="px-5 py-3 max-sm:px-2">{{ (float) $item->confirmed_value }} {{ $item->unit }}</td>
                                <td class="px-5 py-3 max-sm:px-2">{{ (float) $item->garment_value }} {{ $item->unit }}</td>
                                <td class="px-5 py-3 max-sm:px-2">{{ $item->difference > 0 ? '+' : '' }}{{ (float) $item->difference }} {{ $item->unit }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if ($claim->images->isNotEmpty())
            <p class="mb-3 text-lg font-medium max-sm:text-base">@lang('alteration::app.shop.claims.photos')</p>

            <div class="mb-8 flex flex-wrap gap-4">
                @foreach ($claim->images as $image)
                    <a href="{{ $image->url }}" target="_blank" rel="noopener">
                        <img src="{{ $image->url }}" alt="" class="h-28 w-28 rounded-xl border border-zinc-200 object-cover">
                    </a>
                @endforeach
            </div>
        @endif

        @if ($claim->description)
            <p class="mb-3 text-lg font-medium max-sm:text-base">@lang('alteration::app.shop.claims.description')</p>

            <p class="text-base text-zinc-500" v-pre>{!! nl2br(e($claim->description)) !!}</p>
        @endif
    </div>
</x-shop::layouts.account>
