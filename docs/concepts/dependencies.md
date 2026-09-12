---
title: "Dependency injection"
description: "Ask for the services your action needs."
---

# Dependency injection

Handler parameters can include a request, a form request, or services:

```php
public function handle(MessageRequest $request, MessageService $messages): array
{
    return $messages->process($request->validated());
}
```

Here, `MessageService` is an application service you define and import. Bind interfaces in Laravel's container when needed. Contextual bindings for the action are supported during normal dispatch and Precognition dependency resolution.

Constructor injection is supported too. Keep constructors free of writes and other side effects: the action is created **before** method checks and action middleware. Constructors also run when `exert:list` resolves classes. Dependencies that require an action middleware's user or tenant context should be resolved in `handle()` after that middleware runs.

Scalar parameters are not filled from request input. `handle(string $email)` does not read an `email` field automatically. Read validated input from a request instead.

## Bind an interface for one action

Sometimes two actions need different implementations of the same interface. Laravel's contextual binding lets you describe that choice in an application provider:

```php
use App\Contracts\ReceiptSender;
use App\Http\Actions\Orders\SendReceipt;
use App\Services\EmailReceiptSender;

$this->app->when(SendReceipt::class)
    ->needs(ReceiptSender::class)
    ->give(EmailReceiptSender::class);
```

These are application types: define the interface, implementation, and action before using this snippet. The action can then ask for `ReceiptSender` in `handle()`.

Exert preserves this resolution context during predictions too. A contextual `FormRequest` replacement therefore keeps its validation and authorization rules in both request types.

## Constructors run earlier than you might expect

If action middleware signs in a user or selects a tenant, an action constructor has already run by then. A constructor should not assume that action middleware has prepared those values.

Prefer resolving such dependencies in the handler, or move shared setup to route middleware. The [lifecycle page](/reference/lifecycle) shows the exact order.

## Method bindings are an advanced case

Normal dispatch honors Laravel container method bindings for `handle()`. Prediction resolves the declared handler dependencies but intentionally does not execute a method binding. A binding may do real work, so it is not a validation hook.

Put prediction rules in an injected form request. Do not hide essential validation only inside a method binding.
