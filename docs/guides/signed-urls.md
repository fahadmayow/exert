---
title: "Signed URLs"
description: "Make sure the signed operation is the selected operation."
---

# Signed URLs

**Use `query` for endpoints where a signed URL grants access to a particular action.** Include the action key and value when generating the signed URL, and apply Laravel's signature validation middleware.

The URL signature covers the URL, not the JSON or form body. With `body` or `both`, a caller may be able to select a different action without changing the signed URL. Do not exclude the action parameter from signature validation.

A signature also does not replace any user or resource permission checks your operation needs.

## A complete routing example

Use the status action from [Your first endpoint](/start/first-action). In this separate example, register a signed endpoint:

```php
use App\Http\Controllers\SystemController;
use Illuminate\Support\Facades\Route;

Route::exert('/signed-system', SystemController::class, 'GET')
    ->middleware('signed')
    ->name('signed.system');
```

Keep `exert.action_source` set to `query`. Generate a link in trusted server code:

```php
use Illuminate\Support\Facades\URL;

$url = URL::temporarySignedRoute(
    'signed.system',
    now()->addMinutes(10),
    ['action' => 'status'],
);
```

The extra action parameter becomes part of the signed query. Changing it invalidates the signature. Do not generate this link from an unchecked action name supplied by a caller.

## Why the source matters

Consider a URL signed for `action=status`, with a JSON body containing `action=another-operation`. Query selection keeps the selected operation tied to the URL. Body selection or merged input can make the selected operation depend on unsigned data.

Restricting the route to GET is not a substitute for choosing the right source: a request can still carry a JSON body.

## A signature proves integrity, not every permission

A signed link can act as a limited capability, depending on your application. It does not automatically verify record ownership or prevent replay while the link remains valid. Keep any additional access or one-time-use rules your operation requires.

See [Authorization](/concepts/authorization) for record-level checks.
