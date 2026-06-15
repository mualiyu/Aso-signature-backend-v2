@php
    $isDhlShipment = $shipment->carrier_code === 'dhl'
        || $shipment->carrier_title === 'DHL Express'
        || str_starts_with(strtolower((string) ($shipment->carrier_code ?? '')), 'dhl');

    $dhlTrackingUrl = $isDhlShipment && $shipment->track_number
        ? 'https://www.dhl.com/global-en/home/tracking/tracking-express.html?tracking-id=' . urlencode($shipment->track_number)
        : null;
@endphp

@if ($dhlTrackingUrl)
    <p class="pt-2">
        <a
            href="{{ $dhlTrackingUrl }}"
            target="_blank"
            rel="noopener noreferrer"
            class="text-blue-600 hover:underline"
        >
            @lang('admin::app.sales.shipments.view.dhl-track-online')
        </a>
    </p>
@endif

@if ($shipment->dhl_documents_path)
    <p class="pt-2">
        <a
            href="{{ route('admin.sales.shipments.dhl-documents', $shipment->id) }}"
            class="text-blue-600 hover:underline"
        >
            @lang('admin::app.sales.shipments.view.dhl-download')
        </a>
    </p>
@elseif ($isDhlShipment && $shipment->track_number)
    <p class="pt-2 text-sm text-amber-600 dark:text-amber-400">
        @lang('admin::app.sales.shipments.view.dhl-documents-missing')
    </p>
@endif

@if ($isDhlShipment && $shipment->track_number)
    @if ($shipment->dhl_last_checkpoint_description)
        <p class="pt-4 font-semibold text-gray-800 dark:text-white">
            {{ $shipment->dhl_last_checkpoint_description }}
            @if ($shipment->dhl_last_checkpoint_code)
                <span class="text-sm font-normal text-gray-500">({{ $shipment->dhl_last_checkpoint_code }})</span>
            @endif
        </p>

        <p class="text-gray-600 dark:text-gray-300">
            @lang('admin::app.sales.shipments.view.dhl-last-status')
            @if ($shipment->dhl_tracking_fetched_at)
                — {{ core()->formatDate($shipment->dhl_tracking_fetched_at, 'd M, Y H:i') }}
            @endif
        </p>
    @endif

    <p class="pt-2">
        <a
            href="{{ route('admin.sales.shipments.dhl-tracking-refresh', $shipment->id) }}"
            class="text-sm text-blue-600 hover:underline"
        >
            @lang('admin::app.sales.shipments.view.dhl-refresh-tracking')
        </a>
    </p>
@endif
