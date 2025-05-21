<?php

namespace Modules\Accounting\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Factory;

class AccountingServiceProvider extends ServiceProvider
{
    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->registerFactories();
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        $this->app['events']->listen(\App\Events\SellCreatedOrModified::class, 
        \Modules\Accounting\Listeners\MapSellTransaction::class);

        $this->app['events']->listen(\App\Events\SellDeleted::class, 
        \Modules\Accounting\Listeners\MapSellDeleted::class);

        $this->app['events']->listen(\App\Events\SellReturnAdded::class, 
        \Modules\Accounting\Listeners\MapSellReturnTransaction::class);

        $this->app['events']->listen(\App\Events\TransactionPaymentAdded::class, 
        \Modules\Accounting\Listeners\MapPaymentTransaction::class);

        $this->app['events']->listen(\App\Events\TransactionPaymentUpdated::class, 
        \Modules\Accounting\Listeners\MapPaymentTransaction::class);

        $this->app['events']->listen(\App\Events\TransactionPaymentDeleted::class, 
        \Modules\Accounting\Listeners\MapPaymentDeleted::class);

        $this->app['events']->listen(\App\Events\PurchaseCreatedOrModified::class, 
        \Modules\Accounting\Listeners\MapPurchaseTransaction::class);

        $this->app['events']->listen(\App\Events\PurchaseReturnAdded::class, 
        \Modules\Accounting\Listeners\MapPurchaseReturn::class);

        $this->app['events']->listen(\App\Events\ExpenseCreatedOrModified::class, 
        \Modules\Accounting\Listeners\MapExpenseTransaction::class);

        $this->app['events']->listen(\App\Events\ContactCreatedOrModified::class, 
        \Modules\Accounting\Listeners\MapContactAccount::class);

        $this->app['events']->listen(\App\Events\CheckAdded::class, 
        \Modules\Accounting\Listeners\MapChecks::class);

        $this->app['events']->listen(\App\Events\VoucherAdded::class, 
        \Modules\Accounting\Listeners\MapVoucher::class);

        $this->app['events']->listen(\App\Events\CachTransactionAdded::class, 
        \Modules\Accounting\Listeners\MapCashTransaction::class);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(RouteServiceProvider::class);
    }

    /**
     * Register config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->publishes([
            __DIR__.'/../Config/config.php' => config_path('accounting.php'),
        ], 'config');
        $this->mergeConfigFrom(
            __DIR__.'/../Config/config.php', 'accounting'
        );
    }

    /**
     * Register views.
     *
     * @return void
     */
    public function registerViews()
    {
        $viewPath = resource_path('views/modules/accounting');

        $sourcePath = __DIR__.'/../Resources/views';

        $this->publishes([
            $sourcePath => $viewPath,
        ], 'views');

        $this->loadViewsFrom(array_merge(array_map(function ($path) {
            return $path.'/modules/accounting';
        }, config('view.paths')), [$sourcePath]), 'accounting');
    }

    /**
     * Register translations.
     *
     * @return void
     */
    public function registerTranslations()
    {
        $langPath = resource_path('lang/modules/accounting');

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, 'accounting');
        } else {
            $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'accounting');
        }
    }

    /**
     * Register an additional directory of factories.
     *
     * @return void
     */
    public function registerFactories()
    {
        if (! app()->environment('production') && $this->app->runningInConsole()) {
            app(Factory::class)->load(__DIR__.'/../Database/factories');
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }
}
