---
title: "Middleware order"
description: "Two lists, with one important boundary."
---

# Middleware order

Laravel sorts resolved action middleware using its configured middleware priority. It can expand groups and aliases and remove duplicate resolved entries within that action list.

That does not combine the action list with the route list.

```text
route middleware starts
  route bindings and other route checks run
    controller selects the action
      action middleware starts
        handler dependencies are resolved
        handler runs
      action middleware returns
route middleware returns
```

## An example that can surprise you

Suppose route middleware loads a tenant-specific record, but authentication or tenant selection is only in the action's middleware. The route lookup has already happened before the action middleware runs.

Changing middleware priority inside the action cannot fix that order. Move the shared setup to the route, before the route lookup.

## Avoid repeating work accidentally

If you put the same throttle on the route and in the action, it may run twice. If you put an expensive permission check in both places, it may perform its work twice. Deduplication within the action list does not remove a route check that has already run.

This can be intentional: an endpoint-wide limit and a separate per-action limit solve different problems. Give them distinct names and keys so their purpose is clear.

## Errors also travel back through middleware

The action uses Laravel's HTTP pipeline. Exceptions inside it are rendered through the application's exception handler, so surrounding middleware can inspect the resulting error response after `$next($request)` returns.

Do not assume every failure reaches outer middleware as a thrown exception. If your middleware needs to classify the result, inspect the response status as well.

**Related:** [Errors](/reference/errors) and [Lifecycle limits](./lifecycle).
