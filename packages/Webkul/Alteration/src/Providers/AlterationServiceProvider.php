<?php

namespace Webkul\Alteration\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Spatie\ResponseCache\Facades\ResponseCache;
use Webkul\Alteration\AlterationFeature;

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

        $this->registerFeatureSwitch();
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

        $this->mergeConfigFrom(dirname(__DIR__).'/Config/system.php', 'core');
    }

    /**
     * Inject alteration UI into the core order pages through their view render events, so the
     * Shop and Admin views stay untouched. Storefront pieces only appear while the programme is
     * switched on; the admin order panel always shows.
     */
    protected function registerViewHooks(): void
    {
        $shopHooks = [
            'bagisto.shop.customers.account.orders.cancel_button.after' => 'alteration::shop.order.button',
            'bagisto.shop.customers.account.orders.view.before'         => 'alteration::shop.order.panel',
            'bagisto.shop.home.how_it_works.steps.after'                => 'alteration::shop.locator.banner',
        ];

        foreach ($shopHooks as $event => $template) {
            Event::listen($event, function ($viewRenderEventManager) use ($template) {
                if (AlterationFeature::enabled()) {
                    $viewRenderEventManager->addTemplate($template);
                }
            });
        }

        Event::listen('bagisto.admin.sales.order.left_component.before', function ($viewRenderEventManager) {
            $viewRenderEventManager->addTemplate('alteration::admin.claims.order-panel');
        });
    }

    /**
     * Hide the account menu entry while switched off, and drop cached storefront pages whenever
     * configuration is saved so the footer / contact / How it works links follow the switch.
     */
    protected function registerFeatureSwitch(): void
    {
        View::composer('shop::components.layouts.account.navigation', function () {
            if (! AlterationFeature::enabled()) {
                config(['menu.customer' => collect(config('menu.customer'))
                    ->reject(fn ($item) => $item['key'] === 'account.alterations')
                    ->values()
                    ->all(),
                ]);
            }
        });

        Event::listen('core.configuration.save.after', function () {
            try {
                ResponseCache::clear();
            } catch (\Throwable $e) {
                report($e);
            }
        });
    }
}
