<?php

namespace Webkul\Alteration\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AlterationServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        $this->loadRoutesFrom(__DIR__.'/../Routes/admin-routes.php');

        $this->loadRoutesFrom(__DIR__.'/../Routes/shop-routes.php');

        $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'alteration');

        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'alteration');

        $this->registerViewHooks();
    }

    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(dirname(__DIR__).'/Config/alteration.php', 'alteration');

        $this->mergeConfigFrom(dirname(__DIR__).'/Config/admin-menu.php', 'menu.admin');

        $this->mergeConfigFrom(dirname(__DIR__).'/Config/customer-menu.php', 'menu.customer');

        $this->mergeConfigFrom(dirname(__DIR__).'/Config/acl.php', 'acl');
    }

    /**
     * Inject alteration UI into the core order pages through their view render events, so the
     * Shop and Admin views stay untouched.
     */
    protected function registerViewHooks(): void
    {
        Event::listen('bagisto.shop.customers.account.orders.cancel_button.after', function ($viewRenderEventManager) {
            $viewRenderEventManager->addTemplate('alteration::shop.order.button');
        });

        Event::listen('bagisto.shop.customers.account.orders.view.before', function ($viewRenderEventManager) {
            $viewRenderEventManager->addTemplate('alteration::shop.order.panel');
        });

        Event::listen('bagisto.shop.home.how_it_works.steps.after', function ($viewRenderEventManager) {
            $viewRenderEventManager->addTemplate('alteration::shop.locator.banner');
        });

        Event::listen('bagisto.admin.sales.order.left_component.before', function ($viewRenderEventManager) {
            $viewRenderEventManager->addTemplate('alteration::admin.claims.order-panel');
        });
    }
}
