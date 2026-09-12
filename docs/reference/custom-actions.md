---
title: "Custom action implementations"
description: "Use the interface only when you want to own the lifecycle."
---

# Custom action implementations

`ActionInterface` declares only:

```php
public function initiate(\Illuminate\Http\Request $request): mixed;
```

The controller can dispatch any registered class implementing this contract. A class implementing it directly is responsible for its own methods, middleware, and execution rules. Extend `Action` to get the behavior described in this guide.

## The controller trusts the contract

After resolving a mapped object, the controller checks that it implements `ActionInterface`, adds identity attributes, and calls `initiate()`.

The interface does not promise method validation, middleware, Precognition, or a public `handle()` method. Those are features of the base `Action` class.

Use a direct interface implementation only when you deliberately supply your own HTTP execution behavior. Most actions should extend `Action`.

## Inspection has less information

`exert:list` can list a custom interface implementation, but it cannot assume it exposes the base action's method or middleware metadata. Those values appear as unknown, or `null` in JSON.

See [List actions](/commands/list-actions) for the fields exposed by the inspection command.
