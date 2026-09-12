---
title: "An order workflow"
description: "Put validation, authorization, and a state change in their own places."
---

# An order workflow

The message example teaches the mechanics. An order cancellation shows why the separation is useful: valid input is only the first step. The caller must own the right permission, and the order must still be cancellable when you change it.

## What this example assumes

This is an integration example for an existing application. You supply:

- An Eloquent `Order` model with an integer ID and a string `status` field.
- A database that supports the row-locking behavior used below.
- An order policy with a `cancel` ability.
- Authentication configured for the route.

The example only changes database state. Refunds, inventory changes, and notifications would need their own business rules. Do not copy it as a complete commerce system.

## 1. Validate the request shape

Create `app/Http/Requests/CancelOrderRequest.php`:

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CancelOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Shared route middleware signs in the user.
        // The record-specific policy check happens after loading the order.
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return ['order_id' => ['required', 'integer', 'min:1']];
    }
}
```

We check the shape of the ID here. The service will load the current record. If it no longer exists, the real request should fail rather than operate on stale data.

## 2. Keep the change in a service

Create `app/Services/CancelOrderService.php`:

```php
<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class CancelOrderService
{
    public function cancel(int $orderId, Authenticatable $actor): Order
    {
        return DB::transaction(function () use ($orderId, $actor) {
            $order = Order::query()->lockForUpdate()->findOrFail($orderId);

            Gate::forUser($actor)->authorize('cancel', $order);

            if ($order->status !== 'pending') {
                throw ValidationException::withMessages([
                    'order_id' => 'Only pending orders can be cancelled.',
                ]);
            }

            $order->status = 'cancelled';
            $order->save();

            return $order;
        });
    }
}
```

The transaction keeps this database change together. The row lock and status check handle the possibility that another request is changing the same order. Your database and every competing workflow must use a compatible strategy for those guarantees to hold.

The service accepts the actor explicitly. It does not have to discover the user from a hidden request dependency.

## 3. Make the action the HTTP entry point

Create `app/Http/Actions/Orders/CancelOrder.php`:

```php
<?php

namespace App\Http\Actions\Orders;

use App\Http\Requests\CancelOrderRequest;
use App\Services\CancelOrderService;
use Exert\Action;

class CancelOrder extends Action
{
    protected array $methods = ['POST'];

    protected bool $precognition = false;

    public function handle(CancelOrderRequest $request, CancelOrderService $orders): array
    {
        $data = $request->validated();
        $order = $orders->cancel((int) $data['order_id'], $request->user());

        return ['id' => $order->getKey(), 'status' => $order->status];
    }
}
```

This handler reads validated data, calls the service, and chooses a response. It does not need to contain every rule itself.

Prediction is deliberately disabled here. The interesting result depends on a live record and a state transition. You could later add input prediction, but it would not prove that cancellation will succeed. The service must still check the final request.

## 4. Add the operation to a controller

Create `app/Http/Controllers/OrderController.php`:

```php
<?php

namespace App\Http\Controllers;

use App\Http\Actions\Orders\CancelOrder;
use Exert\ActionController;

class OrderController extends ActionController
{
    protected array $actions = [
        'cancel' => CancelOrder::class,
    ];
}
```

Register it in your loaded API routes:

```php
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

Route::exert('/orders', OrderController::class, 'POST')->middleware('auth');
```

Replace `auth` with the appropriate configured guard for your API. The request shape is:

```http
POST /api/orders?action=cancel
Accept: application/json
Content-Type: application/json

{"order_id":42}
```

Include the session or token credentials required by your guard.

## Add another operation without growing this handler

A future `ShipOrder` can have its own form request, permission checks, and service call. Add it as another controller entry once implemented. Cancellation and shipping then have separate files while staying in the same order group.

The action pattern does not automatically give you idempotency, transactions, or safe retries. If the operation charges money or sends external requests, design those guarantees in the business layer and test them explicitly.
