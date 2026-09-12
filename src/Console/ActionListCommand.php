<?php

namespace Exert\Console;

use Closure;
use Exert\Action;
use Exert\ActionController;
use Exert\ActionInterface;
use Illuminate\Console\Command;
use Illuminate\Routing\Router;
use LogicException;

/**
 * Inspect actions behind registered Exert routes without invoking handlers.
 */
class ActionListCommand extends Command
{
    protected $signature = 'exert:list {--json : Output action metadata as JSON}';

    protected $description = 'List registered Exert endpoints and their actions';

    public function handle(Router $router): int
    {
        $rows = [];

        foreach ($router->getRoutes() as $route) {
            [$controllerClass, $method] = array_pad(explode('@', $route->getActionName(), 2), 2, null);

            if (
                $method === 'initiate' &&
                is_string($controllerClass) &&
                is_a($controllerClass, Action::class, true)
            ) {
                $action = $this->laravel->make($controllerClass);
                $methods = $action->getAllowedMethods();

                $rows[] = [
                    'domain' => $route->getDomain(),
                    'uri' => '/'.ltrim($route->uri(), '/'),
                    'route_name' => $route->getName(),
                    'controller' => null,
                    'action' => null,
                    'action_id' => $controllerClass,
                    'class' => $controllerClass,
                    'route_methods' => $route->methods(),
                    'action_methods' => $methods,
                    'effective_methods' => array_values(array_intersect($methods, $route->methods())),
                    'route_middleware' => $this->middlewareLabels($route->gatherMiddleware()),
                    'action_middleware' => $this->middlewareLabels($action->getActionMiddleware()),
                ];

                continue;
            }

            if ($method !== 'resolve' || !is_a($controllerClass, ActionController::class, true)) {
                continue;
            }

            $controller = $this->laravel->make($controllerClass);

            foreach ($controller->getActions() as $name => $actionClass) {
                if (!is_string($actionClass) || !is_a($actionClass, ActionInterface::class, true)) {
                    throw new LogicException($controllerClass.' registers an invalid action: '.$name);
                }

                $action = $this->laravel->make($actionClass);
                // Custom interface implementations may not expose the base Action metadata.
                $methods = $action instanceof Action ? $action->getAllowedMethods() : null;
                $middleware = $action instanceof Action ? $action->getActionMiddleware() : null;

                $rows[] = [
                    'domain' => $route->getDomain(),
                    'uri' => '/'.ltrim($route->uri(), '/'),
                    'route_name' => $route->getName(),
                    'controller' => $controllerClass,
                    'action' => (string) $name,
                    'action_id' => $controllerClass.'::'.$name,
                    'class' => $actionClass,
                    'route_methods' => $route->methods(),
                    'action_methods' => $methods,
                    'effective_methods' => $methods === null ? null : array_values(array_intersect($methods, $route->methods())),
                    'route_middleware' => $this->middlewareLabels($route->gatherMiddleware()),
                    'action_middleware' => $middleware === null ? null : $this->middlewareLabels($middleware),
                ];
            }
        }

        if ($this->option('json')) {
            $this->line(json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));

            return self::SUCCESS;
        }

        if ($rows === []) {
            $this->components->info('No registered Exert actions found.');

            return self::SUCCESS;
        }

        $this->table(
            ['Domain', 'Endpoint', 'Action', 'Methods', 'Class', 'Route middleware', 'Action middleware'],
            array_map(fn (array $row) => [
                $row['domain'] ?? '*',
                $row['uri'],
                $row['action'] ?? '(direct)',
                $row['effective_methods'] === null ? 'Unknown' : (implode('|', $row['effective_methods']) ?: 'None (route blocked)'),
                $row['class'],
                implode(', ', $row['route_middleware']),
                $row['action_middleware'] === null ? 'Unknown' : implode(', ', $row['action_middleware']),
            ], $rows)
        );

        return self::SUCCESS;
    }

    /**
     * Keep configured aliases readable and closures safe to encode in JSON.
     */
    private function middlewareLabels(array $middleware): array
    {
        return array_map(static fn ($item) => $item instanceof Closure
            ? 'Closure'
            : (is_object($item) ? $item::class : (string) $item), $middleware);
    }
}
