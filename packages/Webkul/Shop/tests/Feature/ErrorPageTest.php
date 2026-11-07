<?php

use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Support\Facades\Route;

use function Pest\Laravel\get;
use function Pest\Laravel\getJson;

it('should render the branded 404 page for an unknown storefront url', function () {
    // Act and Assert.
    get('/this-page-does-not-exist')
        ->assertNotFound()
        ->assertViewIs('shop::errors.index')
        ->assertViewHas('errorCode', 404)
        ->assertSeeText(trans('admin::app.errors.404.title'))
        ->assertSeeText(trans('shop::app.errors.go-to-home'));
});

it('should render the branded 404 page for an unknown admin url', function () {
    // Act and Assert.
    get('/'.config('app.admin_url').'/this-page-does-not-exist')
        ->assertNotFound()
        ->assertViewIs('admin::errors.index')
        ->assertViewHas('errorCode', 404);
});

it('should return a json 404 when the client asks for json', function () {
    // Act and Assert.
    getJson('/this-page-does-not-exist')
        ->assertNotFound()
        ->assertJsonPath('title', trans('rest-api::app.shop.errors.404.title'))
        ->assertJsonPath('message', trans('rest-api::app.shop.errors.404.message'));
});

it('should render the branded 500 page for server errors when debugging is off', function () {
    // Arrange.
    config(['app.debug' => false]);

    app()->forgetInstance(ExceptionHandler::class);

    Route::get('/error-page-test', fn () => throw new RuntimeException('Boom'));

    // Act and Assert.
    get('/error-page-test')
        ->assertStatus(500)
        ->assertViewIs('shop::errors.index')
        ->assertViewHas('errorCode', 500)
        ->assertSeeText(trans('admin::app.errors.500.title'));
});
