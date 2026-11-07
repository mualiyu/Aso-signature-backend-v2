<?php

use Webkul\Attribute\Models\Attribute;
use Webkul\Faker\Helpers\Product as ProductFaker;
use Webkul\Product\Models\Product;

use function Pest\Laravel\putJson;

it('should show the production timeline field on the product edit page', function () {
    // Arrange.
    $product = (new ProductFaker)->getSimpleProductFactory()->create();

    // Act and Assert.
    $this->loginAsAdmin();

    $this->get(route('admin.catalog.products.edit', $product->id))
        ->assertOk()
        ->assertSeeText('Production Timeline');
});

it('should update a product without providing a production timeline', function () {
    // Arrange.
    $product = (new ProductFaker)->getSimpleProductFactory()->create();

    // Act and Assert.
    $this->loginAsAdmin();

    putJson(route('admin.catalog.products.update', $product->id), [
        'sku'               => $product->sku,
        'url_key'           => $product->url_key,
        'short_description' => fake()->sentence(),
        'description'       => fake()->paragraph(),
        'name'              => fake()->words(3, true),
        'price'             => fake()->randomFloat(2, 1, 1000),
        'weight'            => fake()->numberBetween(0, 100),
        'channel'           => core()->getCurrentChannelCode(),
        'locale'            => app()->getLocale(),
    ])
        ->assertRedirect(route('admin.catalog.products.index'));

    expect(Product::find($product->id)->production_timeline)->toBeNull();
});

it('should save the production timeline when provided', function () {
    // Arrange.
    $product = (new ProductFaker)->getSimpleProductFactory()->create();

    // Act and Assert.
    $this->loginAsAdmin();

    putJson(route('admin.catalog.products.update', $product->id), [
        'sku'                 => $product->sku,
        'url_key'             => $product->url_key,
        'short_description'   => fake()->sentence(),
        'description'         => fake()->paragraph(),
        'name'                => fake()->words(3, true),
        'price'               => fake()->randomFloat(2, 1, 1000),
        'weight'              => fake()->numberBetween(0, 100),
        'production_timeline' => $timeline = '2-3 weeks',
        'channel'             => core()->getCurrentChannelCode(),
        'locale'              => app()->getLocale(),
    ])
        ->assertRedirect(route('admin.catalog.products.index'));

    $this->assertDatabaseHas('product_attribute_values', [
        'product_id'   => $product->id,
        'attribute_id' => Attribute::where('code', 'production_timeline')->value('id'),
        'text_value'   => $timeline,
    ]);

    expect(Product::find($product->id)->production_timeline)->toBe($timeline);
});
