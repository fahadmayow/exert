---
title: "Direct calls"
description: "Run an action within an HTTP request without selecting it through a controller."
---

# Direct calls

Within an HTTP request, you can call an action without selecting it through a controller:

```php
$response = app(\App\Http\Actions\System\Status::class)
    ->initiate(request());
```

To expose one action directly as an HTTP endpoint, use the fluent route API:

```php
Route::exert('/status')
    ->action(\App\Http\Actions\System\Status::class, 'GET');
```

Laravel supplies the current request, and Exert runs the same action lifecycle as a direct `initiate()` call.

This runs the action's method checks, middleware, dependency resolution, and response conversion. It does not run the resolver, populate Exert's action attributes, or rerun route middleware.

If the request has a route, that route's method restrictions and middleware exclusions still apply. With no route, only the action's methods are used and no route exclusions are supplied.

Calling `handle()` directly skips Exert's lifecycle. For work shared with jobs or console commands, call a separate application service instead.



## Which behavior do you get?

| Behavior | Through controller | Direct `initiate()` |
| --- | --- | --- |
| Read an action name from input | Yes | No |
| Check the controller map | Yes | No |
| Set Exert identity attributes | Yes | No |
| Check methods and run action middleware | Yes | Yes |
| Apply the request route's method restrictions and exclusions | Yes | Yes, if there is a route |
| Resolve dependencies and convert the result | Yes | Yes |

Direct invocation does not rerun route middleware. It also does not clear or replace metadata that may already be on the request from an earlier controller selection. Do not assume metadata describes a directly called action.

A limiter based on `exert.action_id` therefore needs a deliberate identity strategy if you use direct calls. For most endpoints, normal controller dispatch keeps this simpler.
