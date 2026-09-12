---
title: "Routes"
description: "Choose the endpoint, then let the controller choose the action."
---

# Routes

`Route::exert()` registers a normal Laravel route pointing to the controller's `resolve()` method:

```php
Route::exert('/system', SystemController::class, ['GET', 'POST']);
```

The third argument accepts a method string, an array of methods, or `null`:

| Third argument | Route behavior |
| --- | --- |
| `'POST'` | Accept POST requests. |
| `['GET', 'POST']` | Accept the listed methods, with Laravel's normal route behavior. |
| Omitted or `null` | Use Laravel's `Route::any()` methods. |

Method names passed to the macro are case-insensitive. Each selected action still checks its own allowed methods.

The returned route supports Laravel's usual names, middleware, prefixes, domains, and groups:

```php
Route::prefix('internal')->middleware('auth')->group(function () {
    Route::exert('/system', SystemController::class, ['GET', 'POST'])
        ->name('internal.system');
});
```

Import `Route` and your controller where you use these examples. Select a guard that matches your application, such as `auth:sanctum` when Sanctum is installed and configured.

You can also register the resolver yourself:

```php
Route::match(['GET', 'POST'], '/system', [SystemController::class, 'resolve']);
```

Choose one registration for an endpoint. The macro works with Laravel's route cache; it stores a controller handler rather than a route closure.

## Keep routes explicit

The generator does not modify route files. This makes a new action a deliberate addition: define the class, register it in the map, and make sure the endpoint accepts its method.

If two controllers share an action name such as `create`, their routes and controller-qualified metadata keep those operations distinct. A route name identifies the shared endpoint, not the individual action.

For action-specific logs, use [request metadata](/guides/logging). For the relationship between route and action methods, read [HTTP methods](./http-methods).
