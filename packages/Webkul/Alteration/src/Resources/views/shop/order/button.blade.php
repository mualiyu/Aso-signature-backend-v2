@php
    $alterationEligibility = app(\Webkul\Alteration\Services\ClaimEligibility::class)->check($order);
@endphp

@if ($alterationEligibility['eligible'])
    <a
        href="{{ route('shop.customers.account.alterations.create', $order->id) }}"
        class="secondary-button border-zinc-200 px-5 py-3 font-normal"
    >
        @lang('alteration::app.shop.order.report-btn')
    </a>
@endif
