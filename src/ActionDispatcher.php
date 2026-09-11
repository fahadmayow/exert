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

        if (! $request->isPrecognitive()) {
            return $container->call($callback);
        }

        // Resolve dependencies, including FormRequest validation,
        // without invoking the action or a container method binding.
        static::getMethodDependencies($container, $callback);

        abort(204, headers: [
            'Precognition-Success' => 'true',
        ]);
    }
}
