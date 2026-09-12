---
title: "Troubleshooting"
description: "Start with the layer that failed."
---

# Troubleshooting

| Problem | What to check |
| --- | --- |
| Commands or `Route::exert()` are missing | Package installation and service-provider discovery. |
| Action returns 404 | Correct source and key, exact registered name, and class autoloading. Body-only selection does not work with the default `query` source. |
| Action returns 405 | Both the route's methods and the action's methods. GET does not automatically enable HEAD on an action. |
| Invalid action-key error | Use a simple key with ASCII letters, numbers, and underscores, starting with a letter or underscore. |
| Handler dependency cannot be resolved | Container bindings and parameter types. Scalar input and route models are not mapped automatically. |
| Form request returns 403 | Its `authorize()` method and the user's permissions. |
| Middleware alias is unknown | Application aliases, groups, guards, and required packages. |
| Middleware never runs | Use `middleware()`; check route exclusions and test middleware disabling. |
| Prediction returns success without checking expected fields | Enable it on the action, remove outer Precognition middleware, and put input rules in an injected `FormRequest`. |
| Prediction executes the handler | Check that action Precognition is enabled and its middleware has not been excluded or disabled. |
| Web request fails a request-forgery check | Route middleware and the client's CSRF/session credentials. |
| Generated files appear in the wrong directory | `actions_path`, cached configuration, and the application's root namespace. |
| A middleware receives an unexpected result type | Inner middleware should return a response object when stopping early. |

## A quick 404 investigation

1. Confirm the URL reaches the route you registered.
2. Check `exert.action_source`. The default expects the query string.
3. Check the configured key and exact name in the controller map.
4. Check the mapped class's namespace and autoloading.

Do not switch to `both` merely to hide a client mismatch. It changes precedence and has signed-URL implications. Fix the client or choose a source deliberately.

## A quick prediction investigation

Confirm the action sets `$precognition = true`, injects a form request, and keeps middleware enabled. Then compare a normal request and a prediction with the same input.

If the prediction returns success while required fields are missing, check whether the rules only exist inside `handle()` or whether outer route Precognition stopped execution before the action.

If you still cannot explain the result, reduce the endpoint to the [first example](/start/first-action), then add your application middleware and services back one at a time.
