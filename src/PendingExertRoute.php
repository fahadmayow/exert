<?php

namespace Exert;

use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use InvalidArgumentException;

/**
 * Finish registration of an Exert controller or direct action route.
 */
final class PendingExertRoute
{
    public function __construct(
        private Router $router,
        private string $uri,
    ) {
    }

    /**
     * Register an action-controller resolver route.
     */
    public function controller(
        string $controller,
        string|array|null $allowedMethod = null,
    ): Route {
        return $this->register([$controller, 'resolve'], $allowedMethod);
    }

    /**
     * Register an action directly, without controller selection.
     */
    public function action(
        string $action,
        string|array|null $allowedMethod = null,
    ): Route {
        if (!is_a($action, Action::class, true)) {
            throw new InvalidArgumentException(
                $action.' must extend '.Action::class.'.'
            );
        }

        return $this->register([$action, 'initiate'], $allowedMethod);
    }

    /**
     * Register the Laravel route and return it for normal route chaining.
     */
    private function register(
        array $action,
        string|array|null $allowedMethod,
    ): Route {
        return $allowedMethod === null
            ? $this->router->any($this->uri, $action)
            : $this->router->match((array) $allowedMethod, $this->uri, $action);
    }
}
