<?php

use Illuminate\Support\Facades\Hash;
use Webkul\Checkout\Models\Cart;
use Webkul\Checkout\Models\CartItem;
use Webkul\Faker\Helpers\Customer as CustomerFaker;
use Webkul\Faker\Helpers\Product as ProductFaker;

use function Pest\Laravel\get;
use function Pest\Laravel\withSession;

it('should send a guest to the login page and remember the checkout when the cart needs an account', function () {
    // Arrange.
    $product = (new ProductFaker([
        'attributes' => [
            5  => 'new',
            26 => 'guest_checkout',
        ],

        'attribute_value' => [
            'new' => [
                'boolean_value' => true,
            ],

            'guest_checkout' => [
                'boolean_value' => false,
            ],
        ],
    ]))
        ->getSimpleProductFactory()
        ->create();

    $cart = Cart::factory()->create();

    $additional = [
        'product_id' => $product->id,
        'rating'     => '0',
        'is_buy_now' => '0',
        'quantity'   => '1',
    ];

    CartItem::factory()->create([
        'cart_id'           => $cart->id,
        'product_id'        => $product->id,
        'sku'               => $product->sku,
        'quantity'          => $additional['quantity'],
        'name'              => $product->name,
        'price'             => $convertedPrice = core()->convertPrice($price = $product->price),
        'base_price'        => $price,
        'total'             => $convertedPrice * $additional['quantity'],
        'base_total'        => $price * $additional['quantity'],
        'weight'            => $product->weight ?? 0,
        'total_weight'      => ($product->weight ?? 0) * $additional['quantity'],
        'base_total_weight' => ($product->weight ?? 0) * $additional['quantity'],
        'type'              => $product->type,
        'additional'        => $additional,
    ]);

    cart()->setCart($cart);

    cart()->collectTotals();

    // Act and Assert.
    get(route('shop.checkout.onepage.index'))
        ->assertRedirect(route('shop.customer.session.index'))
        ->assertSessionHas('url.intended', route('shop.checkout.onepage.index'));
});

it('should explain why an account is needed on the login page when sent from checkout', function () {
    // Act and Assert.
    withSession(['url.intended' => route('shop.checkout.onepage.index')])
        ->get(route('shop.customer.session.index'))
        ->assertOk()
        ->assertSeeText(trans('shop::app.customers.login-form.checkout.title'))
        ->assertSeeText(trans('shop::app.customers.login-form.checkout.why-title'))
        ->assertSeeText(trans('shop::app.customers.login-form.checkout.reason-fit'))
        ->assertSeeText(trans('shop::app.customers.login-form.checkout.create-account'))
        ->assertDontSeeText(trans('shop::app.customers.login-form.form-login-text'));
});

it('should show the regular login page when not sent from checkout', function () {
    // Act and Assert.
    get(route('shop.customer.session.index'))
        ->assertOk()
        ->assertSeeText(trans('shop::app.customers.login-form.page-title'))
        ->assertSeeText(trans('shop::app.customers.login-form.form-login-text'))
        ->assertDontSeeText(trans('shop::app.customers.login-form.checkout.title'))
        ->assertDontSeeText(trans('shop::app.customers.login-form.checkout.why-title'));
});

it('should explain why an account is needed on the registration page when sent from checkout', function () {
    // Act and Assert.
    withSession(['url.intended' => route('shop.checkout.onepage.index')])
        ->get(route('shop.customers.register.index'))
        ->assertOk()
        ->assertSeeText(trans('shop::app.customers.signup-form.checkout.title'))
        ->assertSeeText(trans('shop::app.customers.signup-form.checkout.description'))
        ->assertSeeText(trans('shop::app.customers.login-form.checkout.why-title'));
});

it('should return the customer to checkout after signing in from checkout', function () {
    // Arrange.
    $customer = (new CustomerFaker)->factory()->create([
        'password' => Hash::make($password = 'admin123'),
    ]);

    // Act and Assert.
    withSession(['url.intended' => route('shop.checkout.onepage.index')])
        ->post(route('shop.customer.session.create'), [
            'email'    => $customer->email,
            'password' => $password,
        ])
        ->assertRedirect(route('shop.checkout.onepage.index'))
        ->assertSessionMissing('url.intended');
});
