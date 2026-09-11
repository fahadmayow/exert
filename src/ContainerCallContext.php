<?php

namespace Exert;

use Closure;
use Illuminate\Container\Container;

/**
 * Reproduce Container::call()'s context without invoking its target.
 *
 * Depends on Laravel's protected getClassForCallable() and buildStack.
 */
final class ContainerCallContext
{
    public static function run(
        Container $container,
        callable $callback,
        Closure $resolve,
    ): mixed {
        $withinContext = Closure::bind(
            static function (
                Container $container,
                callable $callback,
                Closure $resolve,
            ): mixed {
                $pushedToBuildStack = false;

                if (
                    ($className = $container->getClassForCallable($callback))
                    && ! in_array($className, $container->buildStack, true)
                ) {
                    $container->buildStack[] = $className;
                    $pushedToBuildStack = true;
                }

                try {
                    return $resolve();
                } finally {
                    if ($pushedToBuildStack) {
                        array_pop($container->buildStack);
                    }
                }
            },
            null,
            Container::class,
        );

        return $withinContext($container, $callback, $resolve);
    }
}
