---
title: "HTTP methods"
description: "Both the route and the action must agree."
---

# HTTP methods

The base action accepts `GET`. Override `$methods` for other methods:

```php
protected array $methods = ['PUT', 'PATCH'];
```

Exert compares methods without regard to case and removes duplicate entries. List methods explicitly; `'*'` is not an allow-all option.

When the request has a route, the method must be allowed by **both the route and the action**. On an action-level mismatch, Exert returns `405` with their shared methods in `Allow`. If there are none, the header is empty. Laravel can reject the method earlier if the outer route does not accept it.

A GET action does not automatically accept HEAD. Add `'HEAD'` to the action if you want to support it, and ensure the route permits it too.

Use GET for reads. Use an appropriate write method for changes to data; links and browser prefetching can issue GET requests without the user pressing a submit button.

## Think of two checks

Suppose a route accepts POST and PUT, but the chosen action accepts GET and POST:

| Request | What happens |
| --- | --- |
| POST | Both allow it; the action can continue. |
| PUT | The route allows it, but the action returns 405 with `Allow: POST`. |
| GET | Laravel rejects it at the route before Exert selects an action. |

Allowing a method on the action does not open that method on the route. Allowing it on the route does not open it on every action.

## Direct calls still use the request's route

If you call `initiate()` directly with a request that has a route, Exert still uses that route's method list. Only a request without a route uses the action's declared methods alone.

See [Direct calls](/reference/direct-calls) for the other differences from controller dispatch.
