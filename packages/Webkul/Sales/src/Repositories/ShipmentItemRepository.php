<?php

namespace Webkul\Sales\Repositories;

use Illuminate\Support\Facades\Event;
use Webkul\Core\Eloquent\Repository;

class ShipmentItemRepository extends Repository
{
    /**
     * Specify Model class name
     */
    public function model(): string
    {
        return 'Webkul\Sales\Contracts\ShipmentItem';
    }

    /**
     * @param  array  $data
     * @return void
     */
    public function updateProductInventory($data)
    {
        if (! $data['product']) {
            return;
        }

        if (! $data['product']->manage_stock) {
            return;
        }

        $orderedInventory = $data['product']->ordered_inventories()
            ->where('channel_id', $data['shipment']->order->channel->id)
            ->first();

        if ($orderedInventory) {
            if (($orderedQty = $orderedInventory->qty - $data['qty']) < 0) {
                $orderedQty = 0;
            }

            $orderedInventory->update(['qty' => $orderedQty]);
        }

        $inventory = $data['product']->inventories()
            ->where('vendor_id', $data['vendor_id'])
            ->where('inventory_source_id', $data['shipment']->inventory_source_id)
            ->first();

        if (! $inventory) {
            return;
        }

        if (($qty = $inventory->qty - $data['qty']) < 0) {
            $qty = 0;
        }

        $inventory->update(['qty' => $qty]);

        Event::dispatch('catalog.product.update.after', $data['product']);
    }

    /**
     * Inverse of updateProductInventory: restore stock when a shipment is cancelled.
     * Re-adds the full qty uncapped, touching only rows that exist (same touch-surface
     * as the deduction; creation validated stock beforehand so the floor rarely engaged).
     *
     * @param  array  $data
     * @return void
     */
    public function returnProductInventory($data)
    {
        if (! $data['product']) {
            return;
        }

        if (! $data['product']->manage_stock) {
            return;
        }

        $orderedInventory = $data['product']->ordered_inventories()
            ->where('channel_id', $data['shipment']->order->channel->id)
            ->first();

        if ($orderedInventory) {
            $orderedInventory->update(['qty' => $orderedInventory->qty + $data['qty']]);
        }

        $inventory = $data['product']->inventories()
            ->where('vendor_id', $data['vendor_id'])
            ->where('inventory_source_id', $data['shipment']->inventory_source_id)
            ->first();

        if ($inventory) {
            $inventory->update(['qty' => $inventory->qty + $data['qty']]);
        }

        Event::dispatch('catalog.product.update.after', $data['product']);
    }
}
