---
title: "Route parameters"
description: "Receive route values and explicitly bound models in an action handler."
---

# Route parameters

Route parameters are injected into `handle()` by name. For a route such as `/orders/{order}`:

```php
public function handle(string $order): array
{
    return ['order' => $order];
}
```

The argument name must match the route placeholder. Optional route parameters that are absent do not override a handler default:

```php
public function handle(string $order, string $section = 'overview'): array
{
    return compact('order', 'section');
}
```

Services, requests, and form requests continue to resolve through Laravel's container alongside route values.

## Model binding

An object produced by an explicit route binding is passed through to the matching handler argument:

```php
Route::model('order', Order::class);

public function handle(Order $order): array
{
    return ['id' => $order->getKey()];
}
```

Implicit model binding based only on an action's `handle()` signature does not run. Laravel sees the route's controller method as `resolve(Request $request)`, so it does not inspect the selected action signature when applying implicit bindings. Register an explicit route binding, or load and authorize the record yourself.

## Load a record explicitly

Without route binding, a handler can receive the raw identifier and load the record:

```php
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

public function handle(string $order): array
{
    $order = Order::query()->findOrFail($order);
    Gate::authorize('view', $order);

    return ['id' => $order->getKey(), 'status' => $order->status];
}
```

This is a method excerpt for an application with an `Order` model and a `view` policy.

For request-body record IDs, validate the ID in a form request, then load and authorize the record. The [order workflow](/guides/order-workflow) uses that approach.
