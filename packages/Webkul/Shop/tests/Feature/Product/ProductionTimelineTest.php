<?php

use Webkul\Faker\Helpers\Product as ProductFaker;

it('should show the production timeline disclaimer on the product page when no timeline is set', function () {
    // Arrange.
    $product = (new ProductFaker)->getSimpleProductFactory()->create();

    // Act and Assert.
    $this->get('/'.$product->url_key)
        ->assertOk()
        ->assertSeeText(trans('shop::app.products.view.production-timeline'))
        ->assertSeeText(trans('shop::app.products.view.production-timeline-disclaimer'));
});

it('should show the product production timeline together with the disclaimer', function () {
    // Arrange.
    $product = (new ProductFaker([
        'attributes' => [
            100 => 'production_timeline',
        ],
        'attribute_value' => [
            'production_timeline' => [
                'text_value' => $timeline = '2-3 weeks',
            ],
        ],
    ]))->getSimpleProductFactory()->create();

    // Act and Assert.
    $this->get('/'.$product->url_key)
        ->assertOk()
        ->assertSeeText(trans('shop::app.products.view.production-timeline'))
        ->assertSeeText($timeline)
        ->assertSeeText(trans('shop::app.products.view.production-timeline-disclaimer'));
});
