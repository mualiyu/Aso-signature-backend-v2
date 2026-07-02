<?php

use Illuminate\Support\Facades\Mail;
use Webkul\Customer\Models\Customer;
use Webkul\Faker\Helpers\Product as ProductFaker;
use Webkul\Sales\Models\Order;
use Webkul\Sales\Models\OrderComment;
use Webkul\Sales\Models\OrderItem;
use Webkul\Sales\Repositories\OrderRepository;

use function Pest\Laravel\postJson;

it('exposes the forward-only fulfilment workflow transitions', function () {
    $order = new Order;

    $order->status = Order::STATUS_PROCESSING;
    expect($order->getAllowedTransitions())->toBe([Order::STATUS_SENT_FOR_PRODUCTION]);
    expect($order->canTransitionTo(Order::STATUS_SENT_FOR_PRODUCTION))->toBeTrue();
    expect($order->canTransitionTo(Order::STATUS_SHIPPED))->toBeFalse();

    $order->status = Order::STATUS_SHIPPED;
    expect($order->getAllowedTransitions())->toBe([Order::STATUS_COMPLETED]);

    $order->status = Order::STATUS_COMPLETED;
    expect($order->getAllowedTransitions())->toBe([]);
    expect($order->status_label)->toBe('Delivered / Completed');
});

it('advances an order through a valid workflow transition', function () {
    Mail::fake();

    $customer = Customer::factory()->create();

    $order = Order::factory()->create([
        'status'         => Order::STATUS_PROCESSING,
        'customer_id'    => $customer->id,
        'customer_email' => $customer->email,
    ]);

    $this->loginAsAdmin();

    postJson(route('admin.sales.orders.update-status', $order->id), [
        'status' => Order::STATUS_SENT_FOR_PRODUCTION,
    ])
        ->assertRedirect(route('admin.sales.orders.view', $order->id));

    expect($order->fresh()->status)->toBe(Order::STATUS_SENT_FOR_PRODUCTION);

    $this->assertDatabaseHas((new OrderComment)->getTable(), [
        'order_id' => $order->id,
    ]);
});

it('rejects an invalid workflow transition and leaves the status unchanged', function () {
    Mail::fake();

    $customer = Customer::factory()->create();

    $order = Order::factory()->create([
        'status'         => Order::STATUS_PROCESSING,
        'customer_id'    => $customer->id,
        'customer_email' => $customer->email,
    ]);

    $this->loginAsAdmin();

    // processing -> shipped is not a permitted single step.
    postJson(route('admin.sales.orders.update-status', $order->id), [
        'status' => Order::STATUS_SHIPPED,
    ])
        ->assertRedirect(route('admin.sales.orders.view', $order->id));

    expect($order->fresh()->status)->toBe(Order::STATUS_PROCESSING);
});

it('does not let the quantity recompute downgrade a manually advanced order', function () {
    $product = (new ProductFaker([
        'attributes' => [
            5 => 'new',
        ],
        'attribute_value' => [
            'new' => [
                'boolean_value' => true,
            ],
        ],
    ]))
        ->getSimpleProductFactory()
        ->create();

    $customer = Customer::factory()->create();

    $order = Order::factory()->create([
        'status'         => Order::STATUS_SENT_FOR_PRODUCTION,
        'customer_id'    => $customer->id,
        'customer_email' => $customer->email,
    ]);

    OrderItem::factory()->create([
        'order_id'     => $order->id,
        'product_id'   => $product->id,
        'sku'          => $product->sku,
        'type'         => $product->type,
        'name'         => $product->name,
        'qty_ordered'  => 1,
        'qty_invoiced' => 0,
        'qty_shipped'  => 0,
    ]);

    // Recompute (no explicit override) would normally yield "processing"; the guard must keep the
    // manually advanced workflow stage instead.
    app(OrderRepository::class)->updateOrderStatus($order->fresh());

    expect($order->fresh()->status)->toBe(Order::STATUS_SENT_FOR_PRODUCTION);
});
