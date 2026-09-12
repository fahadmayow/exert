---
title: "Action middleware"
description: "Wrap the checks and response behavior for one operation."
---

# Action middleware

Override `middleware()` for checks needed by one action:

```php
protected function middleware(): array
{
    return ['can:export-reports', 'throttle:exports'];
}
```

This example assumes you have defined the `export-reports` ability and the named `exports` rate limiter in your application. Exert does not create them.

Middleware entries may be registered aliases, groups, class names, parameterized names such as `'auth:sanctum'`, or closures. Laravel resolves aliases and groups and applies its configured priority within the action list.

```text
Route middleware
    → Select the action and create it
    → Check HTTP method and handler
    → Action middleware
        → Resolve handler dependencies
        → Run handler, or validate a prediction
        → Convert handler result to a response
    ← Action middleware
← Route middleware
```

## Stop early when the request cannot continue

A closure can return a response without calling the next step:

```php
protected function middleware(): array
{
    return [
        function ($request, $next) {
            if (! config('features.exports')) {
                return response()->json(['message' => 'Exports are unavailable.'], 503);
            }

            return $next($request);
        },
    ];
}
```

This example uses an application setting named `features.exports`; define it in your own configuration. When exports are disabled, the handler does not run.

## Work with the outgoing response

A middleware can also do work after the next step returns:

```php
function ($request, $next) {
    $response = $next($request);
    $response->headers->set('X-Operation', 'export');

    return $response;
}
```

This is a closure entry for the middleware array. The handler's result has already been converted into a response at this point.

::: tip Use the singular method name
Declare `middleware()`, not `middlewares()`. Exert reads the singular method.
:::

See [Ordering](./order), [Exclusions](./exclusions), and [Lifecycle limits](./lifecycle) before using middleware that depends on an earlier setup step or a termination hook.
