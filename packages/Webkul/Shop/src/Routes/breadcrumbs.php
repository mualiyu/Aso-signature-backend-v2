<?php

use Diglactic\Breadcrumbs\Breadcrumbs;
use Diglactic\Breadcrumbs\Generator as BreadcrumbTrail;

/**
 * Profile routes.
 */
Breadcrumbs::for('shop.customer.profile.index', function (BreadcrumbTrail $trail) {
    $trail->push(trans('shop::app.customer.account.profile.index.title'), route('shop.customer.profile.index'));
});

Breadcrumbs::for('shop.customer.profile.edit', function (BreadcrumbTrail $trail) {
    $trail->parent('shop.customer.profile.index');
});

/**
 * Order routes.
 */
Breadcrumbs::for('shop.customer.orders.index', function (BreadcrumbTrail $trail) {
    $trail->parent('shop.customer.profile.index');

    $trail->push(trans('shop::app.customer.account.order.index.page-title'), route('shop.customer.orders.index'));
});

Breadcrumbs::for('shop.customer.orders.view', function (BreadcrumbTrail $trail, $id) {
    $trail->parent('shop.customer.orders.index');
});

/**
 * Downloadable products.
 */
Breadcrumbs::for('shop.customer.downloadable_products.index', function (BreadcrumbTrail $trail) {
    $trail->parent('shop.customer.profile.index');

    $trail->push(trans('shop::app.customer.account.downloadable_products.title'), route('shop.customer.downloadable_products.index'));
});

/**
 * Wishlists.
 */
Breadcrumbs::for('shop.customer.wishlist.index', function (BreadcrumbTrail $trail) {
    $trail->parent('shop.customer.profile.index');

    $trail->push(trans('shop::app.customer.account.wishlist.page-title'), route('shop.customer.wishlist.index'));
});

/**
 * Reviews.
 */
Breadcrumbs::for('shop.customer.reviews.index', function (BreadcrumbTrail $trail) {
    $trail->parent('shop.customer.profile.index');

    $trail->push(trans('shop::app.customer.account.review.index.page-title'), route('shop.customer.reviews.index'));
});

/**
 * Addresses.
 */
Breadcrumbs::for('shop.customer.addresses.index', function (BreadcrumbTrail $trail) {
    $trail->parent('shop.customer.profile.index');

    $trail->push(trans('shop::app.customer.account.address.index.page-title'), route('shop.customer.addresses.index'));
});

Breadcrumbs::for('shop.customer.addresses.create', function (BreadcrumbTrail $trail) {
    $trail->parent('shop.customer.addresses.index');

    $trail->push(trans('shop::app.customer.account.address.create.page-title'), route('shop.customer.addresses.create'));
});

Breadcrumbs::for('shop.customer.addresses.edit', function (BreadcrumbTrail $trail, $id) {
    $trail->parent('shop.customer.addresses.index');

    $trail->push(trans('shop::app.customer.account.address.edit.page-title'), route('shop.customer.addresses.edit', $id));
});

/**
 * Measurements.
 */
Breadcrumbs::for('shop.customer.measurements.index', function (BreadcrumbTrail $trail) {
    $trail->parent('shop.customer.profile.index');

    $trail->push(trans('shop::app.customer.account.measurements.index.page-title'), route('shop.customer.measurements.index'));
});
Breadcrumbs::for('shop.customer.measurements.create', function (BreadcrumbTrail $trail) {
    $trail->parent('shop.customer.measurements.index');

    $trail->push(trans('shop::app.customer.account.measurements.create.page-title'), route('shop.customer.measurements.create'));
});
Breadcrumbs::for('shop.customer.measurements.edit', function (BreadcrumbTrail $trail, $id) {
    $trail->parent('shop.customer.measurements.index');

    $trail->push(trans('shop::app.customer.account.measurements.edit.page-title'), route('shop.customer.measurements.edit', $id));
});
