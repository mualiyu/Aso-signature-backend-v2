@php
    $isDhlShipment = $shipment->carrier_code === 'dhl'
        || $shipment->carrier_title === 'DHL Express'
        || str_starts_with(strtolower((string) ($shipment->carrier_code ?? '')), 'dhl');

    $dhlTrackingUrl = $isDhlShipment && $shipment->track_number
        ? 'https://www.dhl.com/global-en/home/tracking/tracking-express.html?tracking-id=' . urlencode($shipment->track_number)
        : null;

    $hasDocuments = ! empty($shipment->dhl_documents_path);
@endphp

@if ($shipment->track_number || $dhlTrackingUrl || $hasDocuments)
    <div @class([
        'mt-4 rounded-lg border p-4',
        'border-blue-200 bg-blue-50' => $isDhlShipment,
        'border-zinc-200 bg-zinc-50' => ! $isDhlShipment,
    ])>
        <p class="text-sm font-semibold text-zinc-800">
            @lang('shop::app.customers.account.orders.view.shipments.shipping-updates')
        </p>

        @if ($shipment->track_number)
            <p class="mt-2 text-sm text-zinc-600">
                <span class="font-medium text-zinc-800">
                    @lang('shop::app.customers.account.orders.view.shipments.tracking-number'):
                </span>
                {{ $shipment->track_number }}
            </p>
        @endif

        @if ($isDhlShipment && $shipment->dhl_last_checkpoint_description)
            <p class="mt-1 text-sm text-zinc-600">
                <span class="font-medium text-zinc-800">
                    @lang('shop::app.customers.account.orders.view.shipments.dhl-status'):
                </span>
                {{ $shipment->dhl_last_checkpoint_description }}
            </p>
        @endif

        <div class="mt-3 flex flex-col gap-2 sm:flex-row sm:flex-wrap">
            @if ($dhlTrackingUrl)
                <a
                    href="{{ $dhlTrackingUrl }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center justify-center rounded-lg border border-blue-600 px-4 py-2 text-sm font-medium text-blue-700 transition hover:bg-blue-100"
                >
                    @lang('shop::app.customers.account.orders.view.shipments.track-online')
                </a>
            @endif

            {{-- @if ($hasDocuments)
                <a
                    href="{{ route('shop.customers.account.orders.dhl-documents', $shipment->id) }}"
                    class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700"
                >
                    @lang('shop::app.customers.account.orders.view.shipments.dhl-download')
                </a>
            @elseif ($isDhlShipment && $shipment->track_number)
                <p class="text-sm text-amber-700">
                    @lang('shop::app.customers.account.orders.view.shipments.dhl-documents-pending')
                </p>
            @endif --}}
        </div>
    </div>
@endif
