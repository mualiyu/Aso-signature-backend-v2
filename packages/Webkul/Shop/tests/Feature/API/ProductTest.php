<?php

use Pest\Expectation;
use Webkul\Faker\Helpers\Product as ProductFaker;

use function Pest\Laravel\getJson;

it('returns a new products listing', function () {
    // Arrange.
    $newProductOptions = [
        'attributes' => [
            5 => 'new',
        ],

        'attribute_value' => [
            'new' => [
                'boolean_value' => true,
            ],
        ],
    ];

    (new ProductFaker($newProductOptions))
        ->getSimpleProductFactory()
        ->create();

    // Act
    $response = getJson(route('shop.api.products.index', ['new' => 1]))
        ->assertOk()
        ->collect();

    // Assert
    expect($response['data'])->each(function (Expectation $product) {
        return $product->is_new->toBeTrue();
    });
});

it('returns a featured products listing', function () {
    // Arrange.
    $featuredProductOptions = [
        'attributes' => [
            6 => 'featured',
        ],

        'attribute_value' => [
            'featured' => [
                'boolean_value' => true,
            ],
        ],
    ];

    (new ProductFaker($featuredProductOptions))
        ->getSimpleProductFactory()
        ->create();

    // Act
    $response = getJson(route('shop.api.products.index', ['featured' => 1]))
        ->assertOk()
        ->collect();

    // Assert
    expect($response['data'])->each(function (Expectation $product) {
        return $product->is_featured->toBeTrue();
    });
});

it('returns all products listing', function () {
    // Arrange.
    $product = (new ProductFaker)
        ->getSimpleProductFactory()
        ->create();

    // Act and Assert.
    getJson(route('shop.api.products.index'))
        ->assertOk()
        ->assertJsonIsArray('data')
        ->assertJsonFragment([
            'id' => $product->id,
        ]);
});

it('returns hand-arranged products in the given order', function () {
    // Arrange.
    $products = (new ProductFaker)
        ->getSimpleProductFactory()
        ->count(3)
        ->create();

    $ids = [$products[2]->id, $products[0]->id, $products[1]->id];

    // Act.
    $response = getJson(route('shop.api.products.index', [
        'sort'        => 'manual',
        'product_ids' => implode(',', $ids),
    ]))
        ->assertOk()
        ->collect();

    // Assert.
    expect(collect($response['data'])->pluck('id')->all())->toBe($ids);
});

it('returns no products for a manual sort without picked products', function () {
    // Arrange.
    (new ProductFaker)
        ->getSimpleProductFactory()
        ->create();

    // Act and Assert.
    getJson(route('shop.api.products.index', ['sort' => 'manual']))
        ->assertOk()
        ->assertJsonCount(0, 'data');
});
