---
title: "Route parameters"
description: "Read a route value without assuming handler model binding."
---

# Route parameters

Action handler arguments do not receive Laravel controller route-parameter mapping. For a route such as `/orders/{order}`, read the parameter from the request:

```php
$order = $request->route('order');
```

Do not assume that `handle(Order $order)` loads that order. The container may create a model instance instead. Implicit model binding based on an action's handler signature does not run. Use an explicit route binding or load and authorize the record yourself. A route value is a model only if a binding has already resolved it.

## Load a record explicitly

For a route such as `/orders/{order}`, a handler can read and load the record:

```php
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

public function handle(Request $request): array
{
    $order = Order::query()->findOrFail($request->route('order'));
    Gate::authorize('view', $order);

    return ['id' => $order->getKey(), 'status' => $order->status];
}
```

This is a method excerpt for an application with an `Order` model and a `view` policy. It assumes no earlier binding has converted the route value into a model. If you add an explicit route binding, use the resolved model directly instead.

## Why the usual signature behaves differently

Laravel sees the route's controller method as `resolve(Request $request)`. It does not inspect the selected action's `handle()` signature for implicit route binding.

Exert calls the handler through the container. A type hint asks the container for a dependency; it does not tell Exert which URL segment to use.

For request-body record IDs, validate the ID in a form request, then load and authorize the record. The [order workflow](/guides/order-workflow) uses that approach.
