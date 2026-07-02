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

    @if (
        bouncer()->hasPermission('sales.shipments.cancel')
        && ! $shipment->dhl_last_checkpoint_code
    )
        <form
            method="POST"
            ref="cancelShipmentForm{{ $shipment->id }}"
            action="{{ route('admin.sales.shipments.cancel', $shipment->id) }}"
        >
            @csrf
        </form>

        <p class="pt-2">
            <a
                href="javascript:void(0);"
                class="text-sm text-red-600 hover:underline"
                @click="$emitter.emit('open-confirm-modal', {
                    message: '@lang('admin::app.sales.shipments.view.cancel-confirm-msg')',
                    agree: () => {
                        this.$refs['cancelShipmentForm{{ $shipment->id }}'].submit()
                    }
                })"
            >
                @lang('admin::app.sales.shipments.view.cancel-shipment')
            </a>
        </p>
    @endif

    <!-- DHL Pickup Booking -->
    <div class="mt-4 border-t border-gray-200 pt-3 dark:border-gray-800">
        <p class="font-semibold text-gray-800 dark:text-white">
            @lang('admin::app.sales.shipments.view.pickup-title')
        </p>

        @if ($shipment->dhl_pickup_confirmation_number)
            <p class="pt-1 text-gray-600 dark:text-gray-300">
                @lang('admin::app.sales.shipments.view.pickup-booked', ['number' => $shipment->dhl_pickup_confirmation_number])

                @if ($shipment->dhl_pickup_scheduled_at)
                    — {{ core()->formatDate($shipment->dhl_pickup_scheduled_at, 'd M, Y') }}
                @endif
            </p>

            @if (
                bouncer()->hasPermission('sales.shipments.cancel')
                && ! $shipment->dhl_last_checkpoint_code
            )
                <form
                    method="POST"
                    ref="cancelPickupForm{{ $shipment->id }}"
                    action="{{ route('admin.sales.shipments.pickup.cancel', $shipment->id) }}"
                >
                    @csrf
                </form>

                <p class="pt-2">
                    <a
                        href="javascript:void(0);"
                        class="text-sm text-red-600 hover:underline"
                        @click="$emitter.emit('open-confirm-modal', {
                            message: '@lang('admin::app.sales.shipments.view.pickup-cancel-confirm')',
                            agree: () => {
                                this.$refs['cancelPickupForm{{ $shipment->id }}'].submit()
                            }
                        })"
                    >
                        @lang('admin::app.sales.shipments.view.cancel-pickup')
                    </a>
                </p>
            @endif
        @elseif (bouncer()->hasPermission('sales.shipments.create'))
            @php
                $pickupLocation = core()->getConfigData('sales.carriers.dhl.pickup_location') ?: 'reception';
                $pickupCloseTime = core()->getConfigData('sales.carriers.dhl.pickup_close_time') ?: '18:00';
                $pickupLocationType = core()->getConfigData('sales.carriers.dhl.pickup_location_type') ?: 'business';
            @endphp

            <details class="pt-1">
                <summary class="cursor-pointer text-sm text-blue-600 hover:underline">
                    @lang('admin::app.sales.shipments.view.book-pickup')
                </summary>

                <form
                    method="POST"
                    action="{{ route('admin.sales.shipments.pickup.book', $shipment->id) }}"
                    class="mt-2 flex max-w-md flex-col gap-2"
                >
                    @csrf

                    <label class="text-sm text-gray-600 dark:text-gray-300">
                        @lang('admin::app.sales.shipments.view.pickup-date')

                        <input
                            type="date"
                            name="planned_date"
                            required
                            min="{{ now()->format('Y-m-d') }}"
                            value="{{ now()->addDay()->format('Y-m-d') }}"
                            class="mt-1 w-full rounded-md border border-gray-300 px-2.5 py-1.5 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300"
                        >
                    </label>

                    <label class="text-sm text-gray-600 dark:text-gray-300">
                        @lang('admin::app.sales.shipments.view.pickup-close-time')

                        <input
                            type="time"
                            name="close_time"
                            required
                            value="{{ $pickupCloseTime }}"
                            class="mt-1 w-full rounded-md border border-gray-300 px-2.5 py-1.5 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300"
                        >
                    </label>

                    <label class="text-sm text-gray-600 dark:text-gray-300">
                        @lang('admin::app.sales.shipments.view.pickup-location')

                        <input
                            type="text"
                            name="location"
                            required
                            maxlength="80"
                            value="{{ $pickupLocation }}"
                            class="mt-1 w-full rounded-md border border-gray-300 px-2.5 py-1.5 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300"
                        >
                    </label>

                    <label class="text-sm text-gray-600 dark:text-gray-300">
                        @lang('admin::app.sales.shipments.view.pickup-location-type')

                        <select
                            name="location_type"
                            class="mt-1 w-full rounded-md border border-gray-300 px-2.5 py-1.5 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300"
                        >
                            <option value="business" @selected($pickupLocationType === 'business')>
                                @lang('admin::app.sales.shipments.view.pickup-location-business')
                            </option>

                            <option value="residence" @selected($pickupLocationType === 'residence')>
                                @lang('admin::app.sales.shipments.view.pickup-location-residence')
                            </option>
                        </select>
                    </label>

                    <button type="submit" class="primary-button mt-1 w-max px-2.5 py-1.5">
                        @lang('admin::app.sales.shipments.view.book-pickup')
                    </button>
                </form>
            </details>
        @endif
    </div>
@endif
