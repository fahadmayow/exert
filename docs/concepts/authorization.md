---
title: "Authorization"
description: "Decide who may do what, and to which record."
---

# Authorization

An action name is a destination, not a permission. A caller who knows `?action=refund` should still have to prove they may refund the selected order.

There are three useful places for access checks. Pick the place that has the information the check needs.

## 1. Sign in before the endpoint

Put shared authentication on the route:

```php
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

Route::exert('/orders', OrderController::class, 'POST')
    ->middleware('auth');
```

This assumes you have defined `OrderController`. Choose the guard your application uses. An API using Sanctum would normally use its configured guard, such as `auth:sanctum`.

Route authentication also covers unknown actions and method checks that reach the resolver.

## 2. Check an action-wide ability

When a permission does not depend on loading a particular record, action middleware can check it:

```php
protected function middleware(): array
{
    return ['can:export-reports'];
}
```

Define the `export-reports` ability in your application's authorization setup. The name itself does not create a rule.

## 3. Authorize the record being changed

After loading a record, use your policy:

```php
use Illuminate\Support\Facades\Gate;

Gate::authorize('cancel', $order);
```

Here `$order` is an order you have already loaded, and your application supplies its `cancel` policy rule. See [An order workflow](/guides/order-workflow) for how loading, authorization, and the update fit together.

A form request's `authorize()` method is another place to check permissions available before handler execution. It runs during prediction too.

::: warning Keep the real submission protected
A successful prediction does not grant lasting permission. The user, order, or business state may change before submission. Always authorize the real request again.
:::

## A useful rule of thumb

Shared identity belongs on the route. Action-wide abilities fit action middleware. Record-specific permissions belong where you have loaded the record. This keeps each check close to the information it needs without treating the action map as security policy.
