---
title: "Internals"
description: "A short map for package contributors."
---

# Internals

You do not need these details to write an action. They are useful when reviewing framework upgrades or changing dispatch behavior.

## The pieces

| Class | Responsibility |
| --- | --- |
| `ExertServiceProvider` | Merge config, register the route macro, publishing, and commands. |
| `ActionController` | Validate selection, resolve a registered class, and set request identity. |
| `Action` | Methods, middleware, and response conversion. |
| `ActionDispatcher` | Execute normally or resolve dependencies without executing during prediction. |
| `ContainerCallContext` | Preserve the action's contextual dependency-resolution scope during prediction. |
| `ActionInterface` | Declare the `initiate(Request)` entry point. |

## Why a separate prediction path?

Normal dispatch uses the container's `call()` method. Prediction needs the same dependency context but must not execute the callback or a registered method binding.

`ActionDispatcher` resolves dependencies through `BoundMethod` in a temporary context, then stops with a successful prediction response if validation has not already stopped or failed.

## The framework dependency to watch

`ContainerCallContext` uses Laravel's protected `getClassForCallable()` and `buildStack`. It mirrors the installed container's context behavior and restores the stack in `finally`.

Those protected members are a compatibility dependency. When changing supported Laravel versions, check the implementation and run contextual binding, authorization, failed validation, and method-binding isolation tests.

## Response conversion

The action uses `Router::toResponse()` inside the HTTP pipeline. This gives outer action middleware a response object without adding another pair of router preparation events through `prepareResponse()`.

The outer Laravel router still performs its normal response handling.

For contributor commands, see [Package development](/contributing/development).
