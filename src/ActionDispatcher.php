<?php

namespace Exert;

use Illuminate\Container\BoundMethod;
use Illuminate\Container\Container;
use Illuminate\Http\Request;

class ActionDispatcher extends BoundMethod
{
    public static function dispatch(
        Container $container,
        Action $action,
        Request $request,
    ): mixed {
        $callback = [$action, 'handle'];
        $parameters = $request->route()?->parametersWithoutNulls() ?? [];

        if (! $request->isPrecognitive()) {
            return $container->call($callback, $parameters);
        }

        // Resolve dependencies in the same context as Container::call(),
        // without invoking the action or a container method binding.
        ContainerCallContext::run(
            $container,
            $callback,
            fn () => static::getMethodDependencies($container, $callback, $parameters),
        );

        abort(204, headers: [
            'Precognition-Success' => 'true',
        ]);
    }
}
