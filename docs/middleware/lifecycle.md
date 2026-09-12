---
title: "Middleware lifecycle limits"
description: "Know what runs before and after the action."
---

# Middleware lifecycle limits

- Action middleware's `terminate()` method is not called by Laravel's termination mechanism. Put middleware that needs it on the route or in the HTTP kernel.
- Action selection errors and method mismatches happen before action middleware. Checks that must cover these requests belong on the route.
- Injected request parameters come from Laravel's current request binding. Middleware should update that request object rather than pass a replacement object downstream.
- Routes using Laravel's `web` group keep its session and request-forgery protections. Exert does not disable those checks; clients must send the credentials those routes require.

## After the response is not the same as after the handler

Code after `$next($request)` runs while middleware returns through the pipeline. A middleware's `terminate()` method belongs to a later Laravel lifecycle stage.

Exert's action list is not registered with that termination mechanism. If audit logging or cleanup relies on `terminate()`, register that middleware on the route or in the HTTP kernel.

## Method checks happen before action middleware

A POST-only action receiving GET can fail before its action-specific `auth` or throttle runs. This does not execute the handler, but it means those checks do not cover every rejected request.

Put shared protection on the route when even invalid action requests should pass through it.

## Keep the current request object

The pipeline passes a request object, while injected requests come from Laravel's current container binding. Mutating the current object keeps the two views aligned. Passing a newly created request to `$next()` does not automatically replace that binding.

This matters for form requests, input changes, and metadata. If you need isolated direct-action tests, bind the test request as the current request too.
