<?php

namespace Exert;

use Illuminate\Routing\Route as RoutingRoute;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Exert\Console\ActionControllerMakeCommand;
use Exert\Console\ActionMakeCommand;
use Exert\Console\ActionListCommand;

/**
 * Load package defaults and register config publishing and Artisan commands.
 */
class ExertServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Load defaults even when the application has not published the config.
        $this->mergeConfigFrom(__DIR__.'/../config/exert.php', 'exert');
    }

    public function boot(): void
    {
        // Support immediate controller routes and fluent controller/action routes.
        Router::macro('exert', function (
            string $uri,
            ?string $controller = null,
            string|array|null $allowedMethod = null
        ): RoutingRoute|PendingExertRoute {
            // Laravel binds macro closures to the Router instance.
            $pendingRoute = new PendingExertRoute($this, $uri);

            return $controller === null
                ? $pendingRoute
                : $pendingRoute->controller($controller, $allowedMethod);
        });

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/exert.php' => $this->app->configPath('exert.php'),
            ], 'exert-config');

            $this->commands([
                ActionControllerMakeCommand::class,
                ActionMakeCommand::class,
                ActionListCommand::class,
            ]);
        }
    }
}
