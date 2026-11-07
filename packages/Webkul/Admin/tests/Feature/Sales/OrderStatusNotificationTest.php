<?php

use Illuminate\Support\Facades\Mail;
use Webkul\Core\Models\CoreConfig;
use Webkul\Customer\Models\Customer;
use Webkul\Faker\Helpers\Product as ProductFaker;
use Webkul\Sales\Models\Order;
use Webkul\Sales\Models\OrderItem;
use Webkul\Sales\Repositories\OrderRepository;
use Webkul\Shop\Mail\Order\StatusUpdatedNotification;

/**
 * Email notification toggles are stored under the doubled "emails.general.notifications." prefix.
 */
function enableStatusUpdateEmail(): void
{
    CoreConfig::query()->create([
        'code'  => 'emails.general.notifications.emails.general.notifications.order_status_update',
        'value' => 1,
    ]);
}

function makeOrderWithItem(string $status = Order::STATUS_PROCESSING): Order
{
    $product = (new ProductFaker([
        'attributes'      => [5 => 'new'],
        'attribute_value' => ['new' => ['boolean_value' => true]],
    ]))->getSimpleProductFactory()->create();

    $customer = Customer::factory()->create();

    $order = Order::factory()->create([
        'status'         => $status,
        'customer_id'    => $customer->id,
        'customer_email' => $customer->email,
    ]);

    OrderItem::factory()->create([
        'order_id'   => $order->id,
        'product_id' => $product->id,
        'sku'        => $product->sku,
        'type'       => $product->type,
        'name'       => $product->name,
        'additional' => ['locale' => 'en'],
    ]);

    return $order->fresh();
}

it('emails the customer when the order status changes and the toggle is enabled', function () {
    Mail::fake();
    enableStatusUpdateEmail();

    $order = makeOrderWithItem(Order::STATUS_PROCESSING);

    app(OrderRepository::class)->updateOrderStatus($order, Order::STATUS_SENT_FOR_PRODUCTION);

    Mail::assertQueued(StatusUpdatedNotification::class);
});

it('does not email when the status-update toggle is disabled', function () {
    Mail::fake();

    $order = makeOrderWithItem(Order::STATUS_PROCESSING);

    app(OrderRepository::class)->updateOrderStatus($order, Order::STATUS_SENT_FOR_PRODUCTION);

    Mail::assertNotQueued(StatusUpdatedNotification::class);
});

it('does not send the status-update email for cancellation (its own email covers that)', function () {
    Mail::fake();
    enableStatusUpdateEmail();

    $order = makeOrderWithItem(Order::STATUS_PROCESSING);

    app(OrderRepository::class)->updateOrderStatus($order, Order::STATUS_CANCELED);

    Mail::assertNotQueued(StatusUpdatedNotification::class);
});

it('does not email when the status did not actually change', function () {
    Mail::fake();
    enableStatusUpdateEmail();

    $order = makeOrderWithItem(Order::STATUS_SENT_FOR_PRODUCTION);

    // Re-setting the same status must be a no-op (no event, no email).
    app(OrderRepository::class)->updateOrderStatus($order, Order::STATUS_SENT_FOR_PRODUCTION);

    Mail::assertNotQueued(StatusUpdatedNotification::class);
});

it('renders the status-update email with the current status label', function () {
    $order = makeOrderWithItem(Order::STATUS_SHIPPED);

    $html = (new StatusUpdatedNotification($order))->render();

    expect($html)->toContain('Shipped');
});
