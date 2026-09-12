---
title: "Validation"
description: "Check the input before doing the work."
---

# Validation

A client can send a missing message, a negative quantity, or an invalid address. Validation makes the input contract clear before the action performs its work.

## Start with a form request

The first endpoint uses `MessageRequest`. It requires a string no longer than 255 characters:

```php
public function rules(): array
{
    return [
        'message' => ['required', 'string', 'max:255'],
    ];
}
```

The action asks for the form request instead of a plain request:

```php
public function handle(MessageRequest $request): array
{
    return $request->validated();
}
```

Import your application request class. Laravel creates it, checks its authorization, and validates it before `handle()` runs. Use `validated()` to work with the fields your rules accepted.

The [first endpoint](/start/first-action) has the full class definitions if you want to copy a complete example.

## What a failure looks like

For a JSON request with no message, the response normally has status 422 and a field error:

```json
{
  "message": "The message field is required.",
  "errors": {
    "message": ["The message field is required."]
  }
}
```

The exact text depends on your application's language and validation messages. Exert uses Laravel's error handling; it does not replace the response format.

## Validation in a handler

For an action that does not use Precognition, you may validate a plain request in the handler:

```php
public function handle(\Illuminate\Http\Request $request): array
{
    return $request->validate([
        'message' => ['required', 'string', 'max:255'],
    ]);
}
```

::: info Predictions do not enter the handler
Rules inside `handle()` will not run during a prediction. Use an injected `FormRequest` for actions that support Precognition.
:::

## Valid input is not necessarily allowed input

An order ID may be valid and still belong to someone else. A valid amount may still exceed what this user is allowed to refund. Validation checks shape and values; [authorization](./authorization) checks permission. Business rules may also need a fresh database check when the change is made.
