<?php

use Webkul\Designer\Models\Designer;
use Webkul\Designer\Models\DesignerImage;
use Webkul\Faker\Helpers\Product as ProductFaker;

it('should show the designer name and logo linked to the designer page', function () {
    // Arrange.
    $designer = Designer::create([
        'name'     => 'Test Atelier',
        'slug'     => 'test-atelier-'.fake()->uuid(),
        'email'    => fake()->unique()->safeEmail(),
        'password' => 'secret',
        'status'   => 1,
    ]);

    $logo = DesignerImage::create([
        'designer_id' => $designer->id,
        'position'    => 1,
        'src'         => 'designer_images/test-logo.png',
        'alt'         => 'logo_path',
    ]);

    $product = (new ProductFaker)->getSimpleProductFactory()->create([
        'designer_id' => $designer->id,
    ]);

    // Act and Assert.
    $this->get('/'.$product->url_key)
        ->assertOk()
        ->assertSeeText(trans('shop::app.products.view.designer'))
        ->assertSeeText($designer->name)
        ->assertSee(route('shop.designer.view', $designer->slug))
        ->assertSee(Storage::url($logo->src));
});

it('should not show a designer when the product has none', function () {
    // Arrange.
    $designer = Designer::create([
        'name'     => 'Unlinked Atelier '.fake()->uuid(),
        'slug'     => 'unlinked-atelier-'.fake()->uuid(),
        'email'    => fake()->unique()->safeEmail(),
        'password' => 'secret',
        'status'   => 1,
    ]);

    $product = (new ProductFaker)->getSimpleProductFactory()->create([
        'designer_id' => null,
    ]);

    // Act and Assert.
    $this->get('/'.$product->url_key)
        ->assertOk()
        ->assertDontSeeText($designer->name)
        ->assertDontSee(route('shop.designer.view', $designer->slug));
});
