---
title: "Logging and metadata"
description: "Record the operation, not only the shared route."
---

# Logging and metadata

After a registered action is resolved and its contract checked, Exert adds:

| Attribute | Value |
| --- | --- |
| `exert.action` | Registered name, such as `echo`. |
| `exert.action_class` | Registered action class. |
| `exert.controller` | Controller class selecting it. |
| `exert.action_id` | Controller class followed by `::` and the registered name. |

Read them from attributes, not client input:

```php
$actionId = $request->attributes->get('exert.action_id');
```

They are available to action middleware, including on method-mismatch errors after selection. Route middleware can read them after `$next($request)` returns. They are absent when a request is rejected before selection or for an unknown action. The resolver clears previous Exert attributes if the same request is resolved again.

The controller-qualified ID distinguishes equally named actions in different controllers. If a controller is mounted on several routes, include the route name when you need to distinguish those endpoints. Exert provides this metadata but does not export logs or metrics itself.

## Log from action middleware

This closure belongs in an action's `middleware()` array:

```php
function ($request, $next) {
    $started = microtime(true);
    $response = $next($request);

    logger()->info('Action completed', [
        'action_id' => $request->attributes->get('exert.action_id'),
        'status' => $response->getStatusCode(),
        'duration_ms' => round((microtime(true) - $started) * 1000, 1),
        'prediction' => $request->isPrecognitive(),
    ]);

    return $response;
}
```

This records outcomes that return through this middleware. Requests rejected before action middleware will not appear here. If you need those too, add request logging at the route or global level.

The elapsed time in this example covers work after this middleware starts, not the entire HTTP request.

## Keep log data useful

Log the selected identity from attributes rather than copying an arbitrary body value. Avoid writing full request bodies when they contain personal data or credentials.

For metrics, use stable labels such as controller/action IDs and route names. Raw URLs or record IDs can produce a separate metric series for every request.

Exert does not send logs or metrics to a service. These attributes are small integration points for the tools your application already uses.
