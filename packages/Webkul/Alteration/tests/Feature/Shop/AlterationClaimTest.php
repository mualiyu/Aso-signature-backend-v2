<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Webkul\Alteration\Mail\AdminNewClaimNotification;
use Webkul\Alteration\Mail\CustomerClaimNotification;
use Webkul\Alteration\Models\AlterationClaim;
use Webkul\Alteration\Models\AlterationPartner;
use Webkul\Alteration\Services\ClaimEligibility;
use Webkul\Customer\Models\Customer;
use Webkul\Sales\Models\Order;
use Webkul\Sales\Models\OrderMeasurement;
use Webkul\Sales\Models\OrderPayment;

use function Pest\Laravel\get;
use function Pest\Laravel\getJson;
use function Pest\Laravel\post;

/**
 * A delivered order owned by the customer, with a confirmed-measurement snapshot.
 */
function deliveredOrder(Customer $customer, array $attributes = [], bool $withMeasurements = true): Order
{
    $order = Order::factory()->create(array_merge([
        'customer_id'         => $customer->id,
        'customer_email'      => $customer->email,
        'customer_first_name' => $customer->first_name,
        'customer_last_name'  => $customer->last_name,
        'status'              => Order::STATUS_COMPLETED,
        'delivered_at'        => now()->subDays(2),
    ], $attributes));

    OrderPayment::factory()->create(['order_id' => $order->id]);

    if ($withMeasurements) {
        foreach (['chest' => 100, 'waist' => 84, 'sleeve_length' => 62] as $name => $value) {
            OrderMeasurement::create([
                'order_id'    => $order->id,
                'customer_id' => $customer->id,
                'name'        => $name,
                'value'       => $value,
                'unit'        => 'cm',
            ]);
        }
    }

    return $order;
}

function claimPhotos(int $count = 3): array
{
    return collect(range(1, $count))
        ->map(fn ($i) => UploadedFile::fake()->image("garment-{$i}.jpg"))
        ->all();
}

it('shows the report button and claim form on an eligible delivered order', function () {
    $customer = $this->loginAsCustomer();

    $order = deliveredOrder($customer);

    get(route('shop.customers.account.orders.view', $order->id))
        ->assertOk()
        ->assertSeeText(trans('alteration::app.shop.order.report-btn'));

    get(route('shop.customers.account.alterations.create', $order->id))
        ->assertOk()
        ->assertSeeText(trans('alteration::app.shop.claims.comparison'))
        ->assertSeeText('Chest');
});

it('files a claim with the measurement comparison and photos', function () {
    Storage::fake();
    Mail::fake();

    $customer = $this->loginAsCustomer();

    $order = deliveredOrder($customer);

    $chest = $order->measurements()->where('name', 'chest')->first();

    post(route('shop.customers.account.alterations.store', $order->id), [
        'reason'      => 'measurement_mismatch',
        'description' => 'Chest is far too tight.',
        'garment'     => [$chest->id => 94],
        'photos'      => claimPhotos(),
    ])->assertRedirect();

    $claim = AlterationClaim::where('order_id', $order->id)->firstOrFail();

    expect($claim->status)->toBe(AlterationClaim::STATUS_SUBMITTED)
        ->and($claim->customer_id)->toBe($customer->id)
        ->and($claim->delivered_at->toDateString())->toBe($order->delivered_at->toDateString())
        ->and($claim->items)->toHaveCount(1)
        ->and((float) $claim->items->first()->confirmed_value)->toBe(100.0)
        ->and((float) $claim->items->first()->difference)->toBe(-6.0)
        ->and($claim->items->first()->exceedsTolerance())->toBeTrue()
        ->and($claim->images)->toHaveCount(3);

    Storage::assertExists($claim->images->first()->path);

    Mail::assertQueued(AdminNewClaimNotification::class);
    Mail::assertQueued(CustomerClaimNotification::class, fn ($mail) => $mail->type === 'received');
});

it('requires at least one garment measurement for a measurement mismatch', function () {
    Storage::fake();

    $customer = $this->loginAsCustomer();

    $order = deliveredOrder($customer);

    post(route('shop.customers.account.alterations.store', $order->id), [
        'reason'      => 'measurement_mismatch',
        'description' => 'It does not fit.',
        'garment'     => [],
        'photos'      => claimPhotos(),
    ])->assertSessionHasErrors('garment');

    expect(AlterationClaim::where('order_id', $order->id)->exists())->toBeFalse();
});

it('requires the minimum number of photos', function () {
    Storage::fake();

    $customer = $this->loginAsCustomer();

    $order = deliveredOrder($customer);

    post(route('shop.customers.account.alterations.store', $order->id), [
        'reason'      => 'material_quality_mismatch',
        'description' => 'Not the fabric grade I chose.',
        'photos'      => claimPhotos(1),
    ])->assertSessionHasErrors('photos');
});

it('refuses claims after the 7-day window', function () {
    Storage::fake();

    $customer = $this->loginAsCustomer();

    $order = deliveredOrder($customer, ['delivered_at' => now()->subDays(8)]);

    expect(app(ClaimEligibility::class)->check($order)['reason'])->toBe(ClaimEligibility::WINDOW_CLOSED);

    get(route('shop.customers.account.alterations.create', $order->id))
        ->assertRedirect(route('shop.customers.account.orders.view', $order->id));

    post(route('shop.customers.account.alterations.store', $order->id), [
        'reason'      => 'material_quality_mismatch',
        'description' => 'Too late.',
        'photos'      => claimPhotos(),
    ])->assertSessionHasErrors('reason');

    expect(AlterationClaim::where('order_id', $order->id)->exists())->toBeFalse();
});

it('still accepts a claim on the last day of the window', function () {
    $customer = Customer::factory()->create();

    $order = deliveredOrder($customer, ['delivered_at' => now()->subDays(7)->startOfDay()]);

    expect(app(ClaimEligibility::class)->check($order)['eligible'])->toBeTrue();
});

it('is not eligible before delivery or without a measurement snapshot', function () {
    $customer = Customer::factory()->create();

    $shipped = deliveredOrder($customer, ['status' => Order::STATUS_SHIPPED, 'delivered_at' => null]);

    $noMeasurements = deliveredOrder($customer, [], false);

    expect(app(ClaimEligibility::class)->check($shipped)['reason'])->toBe(ClaimEligibility::NOT_DELIVERED)
        ->and(app(ClaimEligibility::class)->check($noMeasurements)['reason'])->toBe(ClaimEligibility::NO_MEASUREMENTS);
});

it('allows only one ASO-covered alteration per order', function () {
    $customer = Customer::factory()->create();

    $order = deliveredOrder($customer);

    AlterationClaim::create([
        'order_id'    => $order->id,
        'customer_id' => $customer->id,
        'reason'      => 'measurement_mismatch',
        'status'      => AlterationClaim::STATUS_COMPLETED,
        'fault'       => AlterationClaim::FAULT_ASO,
    ]);

    expect(app(ClaimEligibility::class)->check($order)['reason'])->toBe(ClaimEligibility::ONE_OFF_USED);
});

it('blocks a second claim while one is open', function () {
    $customer = Customer::factory()->create();

    $order = deliveredOrder($customer);

    AlterationClaim::create([
        'order_id'    => $order->id,
        'customer_id' => $customer->id,
        'reason'      => 'measurement_mismatch',
        'status'      => AlterationClaim::STATUS_UNDER_REVIEW,
    ]);

    expect(app(ClaimEligibility::class)->check($order)['reason'])->toBe(ClaimEligibility::OPEN_CLAIM);
});

it('does not let a customer claim on someone else\'s order', function () {
    $this->loginAsCustomer();

    $order = deliveredOrder(Customer::factory()->create());

    get(route('shop.customers.account.alterations.create', $order->id))->assertNotFound();
});

it('lists only vetted, live partners nearest first', function () {
    $partner = fn (array $attributes) => AlterationPartner::create(array_merge([
        'country'         => 'GB',
        'outreach_status' => 'positive',
        'vetting_status'  => 'vetted',
        'is_active'       => true,
    ], $attributes));

    $partner(['name' => 'Bristol Stitch', 'city' => 'Bristol', 'latitude' => 51.4545, 'longitude' => -2.5879]);
    $partner(['name' => 'Reading Tailors', 'city' => 'Reading', 'latitude' => 51.4543, 'longitude' => -0.9781]);
    $partner(['name' => 'London Fit', 'city' => 'London', 'latitude' => 51.5074, 'longitude' => -0.1278]);
    $partner(['name' => 'Unvetted London', 'city' => 'London', 'vetting_status' => 'pending', 'is_active' => false]);
    $partner(['name' => 'Paused London', 'city' => 'London', 'is_active' => false]);
    $partner(['name' => 'Paris Couture', 'city' => 'Paris', 'country' => 'FR']);

    // Scope to this test's partners so other rows in the database don't affect the order.
    $names = collect(
        getJson(route('shop.api.alterations.partners.search', ['country' => 'GB', 'city' => 'london']))
            ->assertOk()
            ->json('data')
    )->whereIn('name', ['London Fit', 'Reading Tailors', 'Bristol Stitch', 'Unvetted London', 'Paused London', 'Paris Couture'])->values();

    expect($names->pluck('name')->all())->toBe(['London Fit', 'Reading Tailors', 'Bristol Stitch'])
        ->and($names->first()['same_city'])->toBeTrue()
        ->and($names->first())->not->toHaveKey('notes')
        ->and($names->first())->not->toHaveKey('payment_terms');
});

it('ranks partners by distance from a known hub when none are in the searched city', function () {
    foreach ([['Bristol Stitch', 'Bristol', 51.4545, -2.5879], ['Reading Tailors', 'Reading', 51.4543, -0.9781]] as [$name, $city, $lat, $lng]) {
        AlterationPartner::create([
            'name'            => $name, 'city' => $city, 'country' => 'GB', 'latitude' => $lat, 'longitude' => $lng,
            'outreach_status' => 'positive', 'vetting_status' => 'vetted', 'is_active' => true,
        ]);
    }

    $data = collect(getJson(route('shop.api.alterations.partners.search', ['country' => 'GB', 'city' => 'Oxford']))->json('data'))
        ->whereIn('name', ['Reading Tailors', 'Bristol Stitch'])
        ->values()
        ->all();

    expect(collect($data)->pluck('name')->all())->toBe(['Reading Tailors', 'Bristol Stitch'])
        ->and($data[0]['distance_km'])->toBeLessThan(50);
});

it('renders the public tailor locator page', function () {
    get(route('shop.alterations.partners.index'))
        ->assertOk()
        ->assertSeeText(trans('alteration::app.shop.locator.title'));
});
