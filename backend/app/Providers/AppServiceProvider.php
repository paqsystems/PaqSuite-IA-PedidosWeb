<?php

namespace App\Providers;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use PaqSuite\LaravelCore\Providers\PaqSuiteCoreServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(Router $router): void
    {
        foreach (PaqSuiteCoreServiceProvider::tenancyMiddlewareAliases() as $alias => $class) {
            $router->aliasMiddleware($alias, $class);
        }
    }
}
