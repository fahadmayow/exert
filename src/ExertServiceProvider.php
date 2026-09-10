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
        // Register a normal controller route so chaining and route caching work.
        Router::macro('exert', function (
            string $uri,
            string $controller,
            string|array|null $allowedMethod = null
        ): RoutingRoute {
            // Laravel binds macro closures to the Router instance.
            $action = [$controller, 'resolve'];

            return $allowedMethod === null
                ? $this->any($uri, $action)
                : $this->match((array) $allowedMethod, $uri, $action);
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
