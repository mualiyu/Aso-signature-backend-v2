@php
    $alterationClaims = \Webkul\Alteration\Models\AlterationClaim::query()
        ->where('order_id', $order->id)
        ->latest('id')
        ->get();
@endphp

@if ($alterationClaims->isNotEmpty() && bouncer()->hasPermission('alterations.claims'))
    <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
        <p class="mb-3 text-base font-semibold text-gray-800 dark:text-white">
            @lang('alteration::app.admin.claims.order-panel-title')
        </p>

        @foreach ($alterationClaims as $alterationClaim)
            <div class="flex items-center justify-between gap-2 border-b py-2 text-sm text-gray-600 dark:border-gray-800 dark:text-gray-300">
                <span>#{{ $alterationClaim->id }} · @lang('alteration::app.admin.claims.reasons.'.$alterationClaim->reason) · {{ core()->formatDate($alterationClaim->created_at, 'd M Y') }}</span>

                <span class="flex items-center gap-2">
                    <span class="label-processing">@lang('alteration::app.admin.claims.statuses.'.$alterationClaim->status)</span>

                    <a href="{{ route('admin.alterations.claims.view', $alterationClaim->id) }}" class="text-blue-600 hover:underline">@lang('alteration::app.admin.claims.view')</a>
                </span>
            </div>
        @endforeach
    </div>
@endif
