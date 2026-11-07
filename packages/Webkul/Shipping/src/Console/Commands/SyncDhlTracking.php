<?php

namespace Webkul\Shipping\Console\Commands;

use Illuminate\Console\Command;
use Webkul\Sales\Models\Order;
use Webkul\Sales\Models\Shipment;
use Webkul\Shipping\Services\DhlTrackingService;

class SyncDhlTracking extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dhl:sync-tracking';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh DHL tracking for shipped orders so deliveries are detected without an admin refresh';

    /**
     * Execute the console command.
     */
    public function handle(DhlTrackingService $dhlTrackingService): int
    {
        $shipments = Shipment::query()
            ->where('carrier_code', 'dhl')
            ->whereNotNull('track_number')
            ->whereHas('order', fn ($query) => $query->where('status', Order::STATUS_SHIPPED))
            ->get();

        $delivered = 0;

        foreach ($shipments as $shipment) {
            $result = $dhlTrackingService->refreshShipment($shipment);

            if (! $result['success']) {
                $this->warn("Shipment #{$shipment->id}: {$result['error']}");

                continue;
            }

            if ($shipment->order()->value('status') === Order::STATUS_COMPLETED) {
                $delivered++;
            }
        }

        $this->info("Checked {$shipments->count()} shipment(s), {$delivered} newly delivered.");

        return self::SUCCESS;
    }
}
