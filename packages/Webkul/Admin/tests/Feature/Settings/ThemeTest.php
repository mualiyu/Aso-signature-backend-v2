<?php

use Illuminate\Http\UploadedFile;
use Webkul\Faker\Helpers\Product as ProductFaker;
use Webkul\Theme\Models\ThemeCustomization;

use function Pest\Laravel\deleteJson;
use function Pest\Laravel\get;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

it('should returns the theme index page', function () {
    // Act and Assert.
    $this->loginAsAdmin();

    get(route('admin.settings.themes.index'))
        ->assertOk()
        ->assertSeeText(trans('admin::app.settings.themes.index.title'))
        ->assertSeeText(trans('admin::app.settings.themes.index.create-btn'));
});

it('should fail the validation with errors when certain field not provided when store the theme', function () {
    // Act and Assert.
    $this->loginAsAdmin();

    postJson(route('admin.settings.themes.store'))
        ->assertJsonValidationErrorFor('name')
        ->assertJsonValidationErrorFor('sort_order')
        ->assertJsonValidationErrorFor('type')
        ->assertJsonValidationErrorFor('channel_id')
        ->assertJsonValidationErrorFor('theme_code')
        ->assertUnprocessable();
});

it('should fail the validation with errors when correct type not provided when store the theme', function () {
    // Act and Assert.
    $this->loginAsAdmin();

    postJson(route('admin.settings.themes.store'), [
        'type' => 'INVALID_TYPE',
    ])
        ->assertJsonValidationErrorFor('name')
        ->assertJsonValidationErrorFor('sort_order')
        ->assertJsonValidationErrorFor('type')
        ->assertJsonValidationErrorFor('channel_id')
        ->assertJsonValidationErrorFor('theme_code')
        ->assertUnprocessable();
});

it('should store the newly created theme', function () {
    // Arrange.
    $lastThemeId = ThemeCustomization::factory()->create()->id + 1;

    // Act and Assert.
    $this->loginAsAdmin();

    postJson(route('admin.settings.themes.store'), [
        'type'       => $type = fake()->randomElement([
            'product_carousel',
            'category_carousel',
            'image_carousel',
            'footer_links',
            'services_content',
        ]),
        'name'       => $name = fake()->name(),
        'sort_order' => $lastThemeId,
        'channel_id' => $channelId = core()->getCurrentChannel()->id,
        'theme_code' => $themeCode = core()->getCurrentChannel()->theme,
    ])
        ->assertOk()
        ->assertJsonPath('redirect_url', route('admin.settings.themes.edit', $lastThemeId));

    $this->assertModelWise([
        ThemeCustomization::class => [
            [
                'id'         => $lastThemeId,
                'type'       => $type,
                'name'       => $name,
                'channel_id' => $channelId,
                'theme_code' => $themeCode,
            ],
        ],
    ]);
});

it('should fail the validation with errors when correct type not provided when update the theme', function () {
    // Arrange.
    $theme = ThemeCustomization::factory()->create();

    // Act and Assert.
    $this->loginAsAdmin();

    postJson(route('admin.settings.themes.update', $theme->id))
        ->assertJsonValidationErrorFor('name')
        ->assertJsonValidationErrorFor('sort_order')
        ->assertJsonValidationErrorFor('type')
        ->assertJsonValidationErrorFor('channel_id')
        ->assertJsonValidationErrorFor('theme_code')
        ->assertUnprocessable();
});

it('should update the theme customizations', function () {
    $theme = ThemeCustomization::factory()->create();

    $data = [];

    switch ($theme->type) {
        case ThemeCustomization::PRODUCT_CAROUSEL:
            $data[app()->getLocale()] = [
                'options' => [
                    'title'   => fake()->title(),
                    'filters' => [
                        'sort'  => 'name-desc',
                        'limit' => '12',
                        'new'   => '1',
                    ],
                ],
            ];

            break;

        case ThemeCustomization::CATEGORY_CAROUSEL:
            $data[app()->getLocale()] = [
                'options' => [
                    'title'   => fake()->title(),
                    'filters' => [
                        'sort'      => 'desc',
                        'limit'     => '10',
                        'parent_id' => '1',
                    ],
                ],
            ];

            break;

        case ThemeCustomization::IMAGE_CAROUSEL:
            $data[app()->getLocale()] = [
                'options' => [
                    [
                        'title' => fake()->title(),
                        'link'  => fake()->url(),
                        'image' => UploadedFile::fake()->image(fake()->word().'.png', 640, 480, 'png'),
                    ],
                ],
            ];

            break;

        case ThemeCustomization::FOOTER_LINKS:
            $data[app()->getLocale()] = [
                'options' => [
                    'column_1' => [
                        [
                            'url'        => fake()->url(),
                            'title'      => fake()->title(),
                            'sort_order' => '1',
                        ],
                    ],
                ],
            ];

            break;

        case ThemeCustomization::SERVICES_CONTENT:
            $data[app()->getLocale()] = [
                'options' => [
                    [
                        'title'        => fake()->title(),
                        'description'  => fake()->paragraph(),
                        'service_icon' => 'icon-truck',
                    ],
                ],
            ];

            break;
    }

    $data['locale'] = app()->getLocale();
    $data['type'] = $theme->type;
    $data['name'] = $name = fake()->name();
    $data['sort_order'] = '1';
    $data['channel_id'] = core()->getCurrentChannel()->id;
    $data['theme_code'] = core()->getCurrentChannel()->theme;
    $data['status'] = 'on';

    // Act and Assert.
    $this->loginAsAdmin();

    postJson(route('admin.settings.themes.update', $theme->id), $data)
        ->assertRedirect(route('admin.settings.themes.index'))
        ->isRedirection();

    $this->assertModelWise([
        ThemeCustomization::class => [
            [
                'id'   => $theme->id,
                'type' => $theme->type,
                'name' => $name,
            ],
        ],
    ]);
});

it('should delete the theme', function () {
    // Arrange.
    $theme = ThemeCustomization::factory()->create();

    // Act and Assert.
    $this->loginAsAdmin();

    deleteJson(route('admin.settings.themes.delete', $theme->id))
        ->assertOk()
        ->assertJsonPath('message', trans('admin::app.settings.themes.delete-success'));

    $this->assertDatabaseMissing('theme_customizations', [
        'id' => $theme->id,
    ]);

    $this->assertDatabaseMissing('theme_customization_translations', [
        'theme_customization_id' => $theme->id,
    ]);
});

it('should require picked products when a product carousel is manually arranged', function () {
    // Arrange.
    $theme = ThemeCustomization::factory()->create(['type' => ThemeCustomization::PRODUCT_CAROUSEL]);

    $locale = app()->getLocale();

    // Act and Assert.
    $this->loginAsAdmin();

    postJson(route('admin.settings.themes.update', $theme->id), [
        'locale'     => $locale,
        'type'       => $theme->type,
        'name'       => fake()->name(),
        'sort_order' => '1',
        'channel_id' => core()->getCurrentChannel()->id,
        'theme_code' => core()->getCurrentChannel()->theme,
        $locale      => [
            'options' => [
                'title'   => fake()->title(),
                'filters' => [
                    'sort'  => 'manual',
                    'limit' => '12',
                ],
            ],
        ],
    ])
        ->assertJsonValidationErrorFor($locale.'.options.filters.product_ids')
        ->assertUnprocessable();
});

it('should save the hand-arranged product order of a product carousel', function () {
    // Arrange.
    $theme = ThemeCustomization::factory()->create(['type' => ThemeCustomization::PRODUCT_CAROUSEL]);

    $locale = app()->getLocale();

    // Act.
    $this->loginAsAdmin();

    postJson(route('admin.settings.themes.update', $theme->id), [
        'locale'     => $locale,
        'type'       => $theme->type,
        'name'       => fake()->name(),
        'sort_order' => '1',
        'channel_id' => core()->getCurrentChannel()->id,
        'theme_code' => core()->getCurrentChannel()->theme,
        $locale      => [
            'options' => [
                'title'   => fake()->title(),
                'filters' => [
                    'sort'        => 'manual',
                    'limit'       => '12',
                    'product_ids' => '7,3,5',
                ],
            ],
        ],
    ])
        ->assertRedirect(route('admin.settings.themes.index'));

    // Assert.
    expect($theme->fresh()->translate($locale)->options['filters'])
        ->toMatchArray([
            'sort'        => 'manual',
            'product_ids' => '7,3,5',
        ]);
});

it('should list the products a product carousel can be arranged from', function () {
    // Arrange.
    $theme = ThemeCustomization::factory()->create(['type' => ThemeCustomization::PRODUCT_CAROUSEL]);

    $product = (new ProductFaker)->getSimpleProductFactory()->create();

    // Act and Assert.
    $this->loginAsAdmin();

    getJson(route('admin.settings.themes.products', $theme->id).'?'.http_build_query([
        'query' => $product->name,
    ]))
        ->assertOk()
        ->assertJsonFragment(['id' => $product->id]);
});

it('should show the hand-arranged products on the product carousel edit page', function () {
    // Arrange.
    $theme = ThemeCustomization::factory()->create(['type' => ThemeCustomization::PRODUCT_CAROUSEL]);

    $products = (new ProductFaker)->getSimpleProductFactory()->count(2)->create();

    $theme->translateOrNew(app()->getLocale())->options = [
        'title'   => 'Picked for you',
        'filters' => [
            'sort'        => 'manual',
            'limit'       => '12',
            'product_ids' => $products[1]->id.','.$products[0]->id,
        ],
    ];

    $theme->save();

    // Act.
    $this->loginAsAdmin();

    $response = get(route('admin.settings.themes.edit', $theme->id))
        ->assertOk()
        ->assertSeeText(trans('admin::app.settings.themes.edit.manual-products.sort-option'));

    // Assert.
    preg_match('/manualProducts: (\[.*?\]),\n/s', $response->getContent(), $matches);

    expect(collect(json_decode($matches[1], true))->pluck('id')->all())
        ->toBe([$products[1]->id, $products[0]->id]);
});
