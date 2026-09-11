<?php

namespace Exert;

use Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Pipeline;
use Illuminate\Routing\Router;
use LogicException;
use ReflectionMethod;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

/**
 * Provides HTTP method checks, middleware, and dependency injection for actions.
 * Subclasses must define a public handle() method with the parameters they need.
 */
abstract class Action implements ActionInterface
{
    /**
     * Allowed HTTP methods. Override this property in subclasses as needed.
     *
     * @var array<string>
     */
    protected array $methods = ['GET'];

    /**
     * Enable Laravel Precognition for this action.
     */
    protected bool $precognition = false;

    /**
     * Return action middleware aliases, groups, class names, or closures.
     * Laravel resolves the list and applies its configured middleware priority.
     */
    protected function middlewares(): array
    {
        return [];
    }

    /**
     * Expose normalized HTTP methods for dispatch and action inspection.
     */
    public function getAllowedMethods(): array
    {
        return array_values(array_unique(array_map('strtoupper', $this->methods)));
    }

    /**
     * Expose declared middleware without executing it.
     */
    public function getActionMiddleware(): array
    {
        return [
            ...($this->precognition ? [HandlePrecognitiveRequests::class] : []),
            ...$this->middlewares(),
        ];
    }

    /**
     * Check the request method, run middleware, and invoke handle().
     */
    public function initiate(Request $request): mixed
    {
        $allowedMethods = $this->getAllowedMethods();

        if (!in_array(strtoupper($request->method()), $allowedMethods, true)) {
            // Let Laravel render the standard 405 response with an Allow header.
            throw new MethodNotAllowedHttpException(
                $allowedMethods,
                'The request method is not supported for this action.'
            );
        }

        // Enforce the handle() convention at runtime without fixing its signature.
        if (
            !method_exists($this, 'handle') ||
            !(new ReflectionMethod($this, 'handle'))->isPublic()
        ) {
            throw new LogicException(
                static::class . ' must define a public handle() method.'
            );
        }

        $router = app(Router::class);

        $shouldSkipMiddleware = app()->bound('middleware.disable') &&
            app()->make('middleware.disable') === true;

        // Apply route exclusions and Laravel's middleware resolution and priority.
        $middlewares = $shouldSkipMiddleware ? [] : $router->resolveMiddleware(
            $this->getActionMiddleware(),
            $request->route()?->excludedMiddleware() ?? []
        );

        // The HTTP pipeline lets Laravel render exceptions before middleware unwinds.
        // Middleware can return a response early or pass the request to the next step.
        // The dispatcher resolves handle() dependencies and skips execution
        // for precognitive requests.
        // Middleware should mutate the current request rather than replace it.
        return app(Pipeline::class)
            ->send($request)
            ->through($middlewares)
            // Convert action results before they return through HTTP middleware.
            ->then(fn (Request $request) => $router->toResponse(
                $request,
                ActionDispatcher::dispatch(app(), $this, $request)
            ));
    }
}
