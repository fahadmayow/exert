---
title: "Shared route middleware"
description: "Protect and prepare the whole endpoint."
---

# Shared route middleware

Use route middleware for checks that apply to the whole endpoint:

```php
Route::exert('/system', SystemController::class, ['GET', 'POST'])
    ->middleware('auth');
```

Route middleware runs before action selection, so it also covers missing or invalid action names. It is the right place for shared authentication, tenant setup, and endpoint-wide rate limits.

## Why shared checks belong here

The route runs before Exert knows the selected action. It can reject an unauthenticated caller before resolving any action class, and it can initialize context needed by route bindings or other route middleware.

A useful arrangement is:

```text
route: authentication → tenant setup → shared rate limit
                                      ↓
                              selected action
                                      ↓
                       action-specific permission
```

Use your application's middleware classes for tenant setup; Exert does not provide a tenancy system.

## Route groups keep related endpoints consistent

```php
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::exert('/orders', OrderController::class, 'POST');
    Route::exert('/reports', ReportController::class, ['GET', 'POST']);
});
```

This example assumes both application controllers exist. Each still decides its own registered actions and each action still checks its HTTP methods.

Checks that need the final action identity belong later. See [Per-action rate limits](/guides/rate-limits) and [Middleware order](./order).
