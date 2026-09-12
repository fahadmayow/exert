---
title: "What executes during prediction"
description: "Keep validation useful without accidentally doing the operation."
---

# What executes during prediction

The action is created, middleware runs, and handler dependencies are resolved. This includes the form request's authorization and validation. The handler itself and container method bindings for it are not executed.

Rules written inside `handle()` are therefore **not checked** during prediction. Move those rules to a `FormRequest`. If a handler has no validating dependency, prediction can return success without checking any handler input rules.

Keep constructors, dependency resolution, and middleware free of unwanted side effects. Skipping `handle()` does not prevent those earlier steps from running. A successful prediction is not a reservation or permission to skip checks later; the real request must validate and authorize again.

When Precognition is disabled, the header alone does not put the action into prediction mode. Do not send prediction requests to such actions expecting execution to be skipped. The same caution applies if you exclude or disable the middleware that enables prediction.

## Resolve does not mean execute

Exert still needs to construct handler dependencies so a form request can validate. If a service opens a file or writes to a database in its constructor, that side effect can occur during prediction.

A service constructor should establish dependencies. Its explicit business methods should do the work. The action calls those methods only during normal handler execution.

## Contextual bindings stay in place

Prediction uses the action's container context. If the application substitutes a stricter form request for one action, prediction uses that substitute too, including its authorization method.

Container method bindings for `handle()` are different: they are intentionally not invoked during prediction. They are execution hooks, not a safe substitute for declarative input rules.

## Errors and success both clean up context

The temporary action context is restored after dependency resolution, including when validation fails or a successful partial validation stops early. This keeps a later resolution from inheriting the wrong action context.

**Related:** [Dependency injection](/concepts/dependencies) and [Internals](/reference/internals).
