@php
    $alterationEligibility = app(\Webkul\Alteration\Services\ClaimEligibility::class)->check($order);

    $alterationReason = $alterationEligibility['reason'];

    $alterationClaim = $alterationEligibility['claim'];

    $alterationDeadline = $alterationEligibility['deadline'];
@endphp

@if ($alterationReason !== \Webkul\Alteration\Services\ClaimEligibility::NOT_DELIVERED)
    <div class="mt-8 rounded-xl border border-zinc-200 px-5 py-4 max-md:mt-5 max-sm:px-4">
        @if ($alterationClaim)
            <p class="text-base font-medium">
                @lang('alteration::app.shop.order.claim-status', [
                    'id'     => $alterationClaim->id,
                    'status' => trans('alteration::app.admin.claims.statuses.'.$alterationClaim->status),
                ])

                <a href="{{ route('shop.customers.account.alterations.view', $alterationClaim->id) }}" class="text-blue-700 underline">@lang('alteration::app.shop.order.view-claim')</a>
            </p>
        @endif

        @if ($alterationEligibility['eligible'])
            <p class="text-sm text-zinc-500">
                @lang('alteration::app.shop.order.window-open', ['date' => core()->formatDate($alterationDeadline, 'd M Y')])
            </p>
        @elseif ($alterationReason === \Webkul\Alteration\Services\ClaimEligibility::WINDOW_CLOSED)
            <p class="text-sm text-zinc-500">
                @lang('alteration::app.shop.order.window-closed', ['date' => core()->formatDate($alterationDeadline, 'd M Y')])
            </p>
        @elseif ($alterationReason === \Webkul\Alteration\Services\ClaimEligibility::ONE_OFF_USED)
            <p class="text-sm text-zinc-500">
                @lang('alteration::app.shop.claims.errors.one-off-used')
            </p>
        @elseif ($alterationReason === \Webkul\Alteration\Services\ClaimEligibility::NO_MEASUREMENTS)
            <p class="text-sm text-zinc-500">
                @lang('alteration::app.shop.claims.errors.no-measurements')
            </p>
        @endif

        @if (in_array($alterationReason, [
            \Webkul\Alteration\Services\ClaimEligibility::WINDOW_CLOSED,
            \Webkul\Alteration\Services\ClaimEligibility::ONE_OFF_USED,
        ], true) || $alterationClaim?->status === 'customer_fault')
            <a href="{{ route('shop.alterations.partners.index') }}" class="mt-2 inline-block text-sm text-blue-700 underline">@lang('alteration::app.shop.order.find-tailor-link')</a>
        @endif
    </div>
@endif
