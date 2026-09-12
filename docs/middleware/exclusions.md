---
title: "Middleware exclusions"
description: "Remove a check deliberately, and only where intended."
---

# Middleware exclusions

A route's `withoutMiddleware()` exclusions also apply to matching action middleware.

```php
use App\Http\Controllers\ReportController;
use App\Http\Middleware\EnsureCanExport;
use Illuminate\Support\Facades\Route;

Route::exert('/reports', ReportController::class)
    ->withoutMiddleware(EnsureCanExport::class);
```

These are application classes. If a selected action declares `EnsureCanExport`, the route exclusion can remove it from that action's list.

## What gets matched?

Exert passes the route's exclusions through Laravel's middleware resolver. Aliases, groups, and classes use Laravel's normal matching rules. Global middleware is not removed this way.

An exclusion belongs to the current route. The same action reached through another route can still run the check.

::: warning Exclusions remove protection
Do not use an exclusion just to make a failing authorization test pass. Establish why the check is unnecessary on that endpoint, and test the replacement access rules.
:::

## Disabling middleware in tests

Laravel's test helper also affects action middleware:

```php
$this->withoutMiddleware();
```

Exert checks whether the container's `middleware.disable` binding is exactly `true`. When it is, action middleware is skipped, including closures. Selection, method checks, and handler dispatch still run.

Keep middleware enabled when testing access control or Precognition. Disabling the middleware that marks a request as precognitive can let a supposed prediction execute the handler normally.

See [HTTP tests](/testing/http) and [Prediction tests](/testing/precognition).
