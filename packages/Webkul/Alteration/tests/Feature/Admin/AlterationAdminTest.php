<?php

use Illuminate\Support\Facades\Mail;
use Webkul\Alteration\Mail\CustomerClaimNotification;
use Webkul\Alteration\Models\AlterationClaim;
use Webkul\Alteration\Models\AlterationFixType;
use Webkul\Alteration\Models\AlterationPartner;
use Webkul\Customer\Models\Customer;
use Webkul\Sales\Models\Order;
use Webkul\Sales\Models\OrderPayment;
use Webkul\Sales\Repositories\OrderRepository;

use function Pest\Laravel\get;
use function Pest\Laravel\getJson;
use function Pest\Laravel\post;
use function Pest\Laravel\put;

function partnerPayload(array $overrides = []): array
{
    return array_merge([
        'name'              => 'London Fit',
        'city'              => 'London',
        'country'           => 'GB',
        'latitude'          => '51.5074',
        'longitude'         => '-0.1278',
        'outreach_status'   => 'positive',
        'vetting_status'    => 'pending',
        'vetting_checklist' => [],
        'is_active'         => 0,
    ], $overrides);
}

function vettedPartner(array $attributes = []): AlterationPartner
{
    return AlterationPartner::create(array_merge([
        'name'            => 'London Fit',
        'city'            => 'London',
        'country'         => 'GB',
        'latitude'        => 51.5074,
        'longitude'       => -0.1278,
        'outreach_status' => 'positive',
        'vetting_status'  => 'vetted',
        'is_active'       => true,
    ], $attributes));
}

function claimForNewOrder(array $attributes = []): AlterationClaim
{
    $customer = Customer::factory()->create();

    $order = Order::factory()->create([
        'customer_id'         => $customer->id,
        'customer_email'      => $customer->email,
        'customer_first_name' => $customer->first_name,
        'customer_last_name'  => $customer->last_name,
        'status'              => Order::STATUS_COMPLETED,
        'delivered_at'        => now()->subDay(),
    ]);

    OrderPayment::factory()->create(['order_id' => $order->id]);

    return AlterationClaim::create(array_merge([
        'order_id'      => $order->id,
        'customer_id'   => $customer->id,
        'reason'        => 'measurement_mismatch',
        'status'        => AlterationClaim::STATUS_SUBMITTED,
        'payout_status' => AlterationClaim::PAYOUT_NOT_APPLICABLE,
    ], $attributes));
}

function decision(array $overrides = []): array
{
    return array_merge([
        'status'        => 'under_review',
        'payout_status' => 'not_applicable',
    ], $overrides);
}

it('renders the partner, fix type and claim screens', function () {
    $this->loginAsAdmin();

    $claim = claimForNewOrder();

    get(route('admin.alterations.partners.index'))->assertOk()->assertSeeText(trans('alteration::app.admin.partners.title'));
    get(route('admin.alterations.partners.create'))->assertOk();
    get(route('admin.alterations.fix_types.index'))->assertOk()->assertSee('Hem (shorten / lengthen)');
    get(route('admin.alterations.claims.index'))->assertOk();
    get(route('admin.alterations.claims.view', $claim->id))->assertOk()->assertSeeText(trans('alteration::app.admin.claims.fault-guide-title'));
    get(route('admin.sales.orders.view', $claim->order_id))->assertOk()->assertSeeText(trans('alteration::app.admin.claims.order-panel-title'));
});

it('loads the partner and claim datagrids', function () {
    $this->loginAsAdmin();

    $claim = claimForNewOrder(['status' => AlterationClaim::STATUS_COMPLETED, 'fault' => 'aso', 'actual_cost' => 42]);

    vettedPartner();

    getJson(route('admin.alterations.claims.index'), ['X-Requested-With' => 'XMLHttpRequest'])
        ->assertOk()
        ->assertJsonPath('records.0.id', $claim->id);

    getJson(route('admin.alterations.partners.index'), ['X-Requested-With' => 'XMLHttpRequest'])
        ->assertOk()
        ->assertJsonFragment(['name' => 'London Fit']);
});

it('saves forms submitted with blank fields the way a browser sends them', function () {
    $this->loginAsAdmin();

    $claim = claimForNewOrder();

    // Exactly what the claim decision form posts when nothing optional is filled in.
    put(route('admin.alterations.claims.update', $claim->id), [
        'status'                => 'under_review',
        'fault_notes'           => '',
        'customer_message'      => '',
        'alteration_partner_id' => '',
        'quoted_cost'           => '',
        'cap_override'          => '0',
        'cap_override_note'     => '',
        'actual_cost'           => '',
        'payout_status'         => 'not_applicable',
        'paid_at'               => '',
        'payout_reference'      => '',
        'admin_notes'           => '',
    ])->assertSessionHasNoErrors()->assertRedirect(route('admin.alterations.claims.view', $claim->id));

    $claim->refresh();

    expect($claim->status)->toBe('under_review')
        ->and($claim->alteration_partner_id)->toBeNull()
        ->and($claim->quoted_cost)->toBeNull();

    $hem = AlterationFixType::where('code', 'hem')->firstOrFail();

    post(route('admin.alterations.partners.store'), partnerPayload([
        'name'         => 'Blank Fields Tailor',
        'contact_name' => '',
        'email'        => '',
        'latitude'     => '',
        'longitude'    => '',
        'currency'     => '',
        'notes'        => '',
        'prices'       => [$hem->id => ['price' => '', 'turnaround_days' => '']],
    ]))->assertSessionHasNoErrors();

    $partner = AlterationPartner::where('name', 'Blank Fields Tailor')->firstOrFail();

    expect($partner->latitude)->toBeNull()
        ->and($partner->prices)->toHaveCount(0);

    put(route('admin.alterations.fix_types.update'), [
        'fix_types' => [$hem->id => ['name' => $hem->name, 'aso_cap_amount' => '', 'currency' => '', 'sort' => '1', 'is_active' => '1']],
        'new'       => ['name' => '', 'aso_cap_amount' => '', 'currency' => ''],
    ])->assertSessionHasNoErrors();

    expect($hem->refresh()->aso_cap_amount)->toBeNull();
});

it('creates a partner with its price sheet', function () {
    $this->loginAsAdmin();

    $hem = AlterationFixType::where('code', 'hem')->firstOrFail();

    post(route('admin.alterations.partners.store'), partnerPayload([
        'prices' => [$hem->id => ['price' => '25', 'turnaround_days' => '3']],
    ]))->assertRedirect(route('admin.alterations.partners.index'));

    $partner = AlterationPartner::where('name', 'London Fit')->latest('id')->firstOrFail();

    expect($partner->prices)->toHaveCount(1)
        ->and((float) $partner->prices->first()->price)->toBe(25.0)
        ->and($partner->prices->first()->turnaround_days)->toBe(3);
});

it('only allows vetting once the checklist is complete and coordinates are set', function () {
    $admin = $this->loginAsAdmin();

    post(route('admin.alterations.partners.store'), partnerPayload([
        'vetting_status'    => 'vetted',
        'vetting_checklist' => ['sample_work_reviewed'],
    ]))->assertSessionHasErrors('vetting_status');

    post(route('admin.alterations.partners.store'), partnerPayload([
        'vetting_status'    => 'vetted',
        'vetting_checklist' => config('alteration.vetting_checklist'),
        'latitude'          => null,
        'longitude'         => null,
    ]))->assertSessionHasErrors('latitude');

    post(route('admin.alterations.partners.store'), partnerPayload([
        'is_active' => 1,
    ]))->assertSessionHasErrors('is_active');

    post(route('admin.alterations.partners.store'), partnerPayload([
        'name'              => 'Vetted Tailor',
        'vetting_status'    => 'vetted',
        'vetting_checklist' => config('alteration.vetting_checklist'),
        'is_active'         => 1,
    ]))->assertSessionHasNoErrors();

    $partner = AlterationPartner::where('name', 'Vetted Tailor')->firstOrFail();

    expect($partner->is_active)->toBeTrue()
        ->and($partner->vetted_by_admin_id)->toBe($admin->id)
        ->and($partner->vetted_at)->not->toBeNull();
});

it('takes a partner off the storefront when it is un-vetted', function () {
    $this->loginAsAdmin();

    $partner = vettedPartner(['vetting_checklist' => config('alteration.vetting_checklist'), 'vetted_at' => now()]);

    put(route('admin.alterations.partners.update', $partner->id), partnerPayload([
        'vetting_status' => 'rejected',
    ]))->assertSessionHasNoErrors();

    $partner->refresh();

    expect($partner->is_active)->toBeFalse()
        ->and($partner->vetted_at)->toBeNull();
});

it('requires a reason before classifying a claim as ASO fault and emails the customer', function () {
    Mail::fake();

    $this->loginAsAdmin();

    $claim = claimForNewOrder();

    put(route('admin.alterations.claims.update', $claim->id), decision(['status' => 'aso_fault_approved']))
        ->assertSessionHasErrors('fault_notes');

    put(route('admin.alterations.claims.update', $claim->id), decision([
        'status'      => 'aso_fault_approved',
        'fault_notes' => 'Chest cut 6 cm under the confirmed spec.',
    ]))->assertSessionHasNoErrors();

    $claim->refresh();

    expect($claim->fault)->toBe(AlterationClaim::FAULT_ASO)
        ->and($claim->classified_at)->not->toBeNull();

    Mail::assertQueued(CustomerClaimNotification::class, fn ($mail) => $mail->type === 'updated');
});

it('sets customer fault from the status', function () {
    Mail::fake();

    $this->loginAsAdmin();

    $claim = claimForNewOrder();

    put(route('admin.alterations.claims.update', $claim->id), decision(['status' => 'customer_fault']))
        ->assertSessionHasNoErrors();

    expect($claim->refresh()->fault)->toBe(AlterationClaim::FAULT_CUSTOMER);
});

it('refuses a second ASO-covered claim on the same order', function () {
    $this->loginAsAdmin();

    $first = claimForNewOrder(['status' => AlterationClaim::STATUS_COMPLETED, 'fault' => AlterationClaim::FAULT_ASO]);

    $second = AlterationClaim::create([
        'order_id'      => $first->order_id,
        'customer_id'   => $first->customer_id,
        'reason'        => 'measurement_mismatch',
        'status'        => AlterationClaim::STATUS_SUBMITTED,
        'payout_status' => AlterationClaim::PAYOUT_NOT_APPLICABLE,
    ]);

    put(route('admin.alterations.claims.update', $second->id), decision([
        'status'      => 'aso_fault_approved',
        'fault_notes' => 'Second fault.',
    ]))->assertSessionHasErrors('status');

    expect($second->refresh()->status)->toBe(AlterationClaim::STATUS_SUBMITTED);
});

it('holds partner quotes to the fix type cap unless overridden', function () {
    Mail::fake();

    $this->loginAsAdmin();

    $partner = vettedPartner();

    $hem = AlterationFixType::where('code', 'hem')->firstOrFail();
    $hem->update(['aso_cap_amount' => 30]);

    $claim = claimForNewOrder(['status' => AlterationClaim::STATUS_ASO_FAULT_APPROVED, 'fault' => AlterationClaim::FAULT_ASO, 'fault_notes' => 'Hem too short.']);

    $assign = fn (array $overrides = []) => put(route('admin.alterations.claims.update', $claim->id), decision(array_merge([
        'status'                => 'partner_assigned',
        'fault_notes'           => 'Hem too short.',
        'alteration_partner_id' => $partner->id,
        'fix_type_ids'          => [$hem->id],
        'quoted_cost'           => '45',
    ], $overrides)));

    $assign()->assertSessionHasErrors('quoted_cost');

    $assign(['cap_override' => 1])->assertSessionHasErrors('quoted_cost');

    $assign(['cap_override' => 1, 'cap_override_note' => 'Only partner in the city.'])->assertSessionHasNoErrors();

    $claim->refresh();

    expect((float) $claim->approved_cap_amount)->toBe(30.0)
        ->and($claim->alteration_partner_id)->toBe($partner->id)
        ->and($claim->payout_status)->toBe(AlterationClaim::PAYOUT_PENDING);
});

it('only assigns vetted, live partners', function () {
    $this->loginAsAdmin();

    $pending = vettedPartner(['vetting_status' => 'pending', 'is_active' => false]);

    $claim = claimForNewOrder(['status' => AlterationClaim::STATUS_ASO_FAULT_APPROVED, 'fault' => AlterationClaim::FAULT_ASO]);

    put(route('admin.alterations.claims.update', $claim->id), decision([
        'status'                => 'partner_assigned',
        'fault_notes'           => 'Production error.',
        'alteration_partner_id' => $pending->id,
    ]))->assertSessionHasErrors('alteration_partner_id');
});

it('stamps delivered_at the first time an order is completed', function () {
    $order = Order::factory()->create(['status' => Order::STATUS_SHIPPED, 'delivered_at' => null]);

    app(OrderRepository::class)->updateOrderStatus($order, Order::STATUS_COMPLETED);

    $deliveredAt = $order->refresh()->delivered_at;

    expect($deliveredAt)->not->toBeNull();

    $this->travel(2)->days();

    app(OrderRepository::class)->updateOrderStatus($order, Order::STATUS_COMPLETED);

    expect($order->refresh()->delivered_at->equalTo($deliveredAt))->toBeTrue();
});
