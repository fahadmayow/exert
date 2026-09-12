---
title: "Responses"
description: "Return the result that fits the HTTP operation."
---

# Responses

Return the value that best fits the HTTP result:

| Return value | Result |
| --- | --- |
| Array | JSON response. |
| String | Normal response body. |
| Laravel view | Rendered view. |
| JSON response or redirect | The supplied response. |
| Streamed or file response | The supplied response, prepared for the request. |
| Laravel `Responsable` object | Converted using its `toResponse()` method. |

Exert uses Laravel's `Router::toResponse()` before the handler result returns through action middleware. Middleware can therefore work with the response returned by `$next($request)`.

Laravel's usual rules still apply to status codes, content types, HEAD responses, and response preparation. Exert does not force every result into a custom JSON format.

## Pick a status deliberately

A plain array is useful for an ordinary successful result. When you need a specific status or header, build the response yourself:

```php
return response()->json(['accepted' => true], 202);
```

A command that has completed without a response body can return:

```php
return response()->noContent();
```

For a browser workflow, a redirect is also valid:

```php
return redirect('/orders');
```

Choose the response based on what actually happened. For example, use 202 when work was accepted for later processing, not simply because the request was a POST.

## Middleware sees the converted response

An action returning an array does not require every middleware to understand arrays. Exert converts the handler result before it returns through action middleware, so middleware can add headers or inspect the status.

An early return from middleware skips that destination. Such middleware should return a response object itself. See [Action middleware](/middleware/actions).

## Keep private data out of the response

Return the fields the caller needs. If you return models, Laravel's serialization rules still apply. Exert does not strip secrets or enforce resource policies for you.
