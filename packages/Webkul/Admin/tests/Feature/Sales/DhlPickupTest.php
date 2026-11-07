<?php

use Illuminate\Support\Facades\Http;
use Webkul\Core\Models\CoreConfig;
use Webkul\Customer\Models\Customer;
use Webkul\Sales\Models\Order;
use Webkul\Sales\Models\OrderAddress;
use Webkul\Sales\Models\Shipment;
use Webkul\Shipping\Services\DhlPickupService;

/**
 * Seed the DHL carrier settings the pickup builder needs. Most DHL fields are channel_based, so the
 * rows must carry the current channel code for core()->getConfigData() to resolve them.
 */
function seedDhlConfig(): void
{
    $channel = core()->getRequestedChannelCode();

    $values = [
        'api_key'        => 'test-key',
        'api_secret'     => 'test-secret',
        'account_number' => '123456789',
        'sandbox_mode'   => '1',
        'origin_country' => 'NG',
        'origin_postcode'=> '900001',
        'origin_city'    => 'Abuja',
        'origin_address' => '1 Store Street',
        'origin_company' => 'Aso Signature',
        'origin_email'   => 'store@example.com',
        'origin_phone'   => '+2348000000000',
    ];

    foreach ($values as $key => $value) {
        CoreConfig::query()->create([
            'code'         => 'sales.carriers.dhl.'.$key,
            'value'        => $value,
            'channel_code' => $channel,
            'locale_code'  => null,
        ]);
    }
}

function makeDhlShipment(): Shipment
{
    $customer = Customer::factory()->create();

    $order = Order::factory()->create([
        'status'          => Order::STATUS_SHIPPED,
        'customer_id'     => $customer->id,
        'customer_email'  => $customer->email,
        'base_grand_total'=> 50000,
    ]);

    OrderAddress::factory()->create([
        'order_id'     => $order->id,
        'customer_id'  => $customer->id,
        'address_type' => OrderAddress::ADDRESS_TYPE_SHIPPING,
        'country'      => 'NG',
        'postcode'     => '900001',
        'city'         => 'Abuja',
        'first_name'   => 'Jane',
        'last_name'    => 'Doe',
        'phone'        => '+2348011111111',
    ]);

    return Shipment::factory()->create([
        'order_id'              => $order->id,
        'customer_id'           => $customer->id,
        'customer_type'         => Customer::class,
        'carrier_code'          => 'dhl',
        'carrier_title'         => 'DHL Express',
        'track_number'          => '1234567890',
        'inventory_source_id'   => 1,
        'inventory_source_name' => 'Default',
        'total_qty'             => 1,
        'total_weight'          => 1,
    ]);
}

it('books a DHL pickup against the /pickups endpoint and returns the dispatch number', function () {
    seedDhlConfig();

    Http::fake([
        '*/pickups' => Http::response([
            'dispatchConfirmationNumbers' => ['CBJ250812000001'],
            'readyByTime'                 => '2026-07-10T10:00:00',
            'nextPickupDate'              => '2026-07-11',
        ], 201),
    ]);

    $shipment = makeDhlShipment();

    $result = app(DhlPickupService::class)->bookPickup($shipment, [
        'planned_date'  => '2026-07-10',
        'close_time'    => '18:00',
        'location'      => 'reception',
        'location_type' => 'business',
    ]);

    expect($result['success'])->toBeTrue();
    expect($result['data']['dispatch_confirmation_number'])->toBe('CBJ250812000001');

    Http::assertSent(function ($request) {
        $body = $request->data();

        return str_ends_with($request->url(), '/pickups')
            && $request->method() === 'POST'
            && ! empty($body['plannedPickupDateAndTime'])
            && $body['closeTime'] === '18:00'
            && $body['location'] === 'reception'
            && $body['locationType'] === 'business'
            && isset($body['accounts'][0]['number'])
            && isset($body['customerDetails']['shipperDetails']['postalAddress'])
            && isset($body['customerDetails']['receiverDetails']['postalAddress'])
            && isset($body['shipmentDetails'][0]['packages'][0]['weight']);
    });
});

it('cancels a booked DHL pickup against the /pickups/{id} endpoint', function () {
    seedDhlConfig();

    Http::fake([
        '*/pickups/*' => Http::response([], 204),
    ]);

    $shipment = makeDhlShipment();
    $shipment->update(['dhl_pickup_confirmation_number' => 'CBJ250812000001']);

    $result = app(DhlPickupService::class)->cancelPickup($shipment);

    expect($result['success'])->toBeTrue();

    Http::assertSent(function ($request) {
        return str_contains($request->url(), '/pickups/CBJ250812000001')
            && $request->method() === 'DELETE';
    });
});

it('treats a missing pickup confirmation number as a no-op cancel', function () {
    seedDhlConfig();

    Http::fake();

    $shipment = makeDhlShipment();

    $result = app(DhlPickupService::class)->cancelPickup($shipment);

    expect($result['success'])->toBeTrue();
    expect($result['skipped'] ?? false)->toBeTrue();

    Http::assertNothingSent();
});
