---
title: "Per-action rate limits"
description: "Give each operation its own budget when it needs one."
---

# Per-action rate limits

Register a named limiter in an application provider's `boot()` method:

```php
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

RateLimiter::for('exert-operation', function (Request $request) {
    $actionId = $request->attributes->get('exert.action_id');
    abort_if($actionId === null, 500, 'Action identity is required.');

    $userId = $request->user()?->getAuthIdentifier();
    $actor = $userId !== null ? 'user:'.$userId : 'ip:'.$request->ip();

    return Limit::perMinute(30)->by($actionId.'|'.$actor);
});
```

Apply it inside the action:

```php
protected function middleware(): array
{
    return ['throttle:exert-operation'];
}
```

This gives each actor a separate counter for each controller/action pair. Put shared authentication on the route so the limiter sees the authenticated user.

A limiter using Exert attributes must run after selection. It will not work as outer route middleware or during a direct call that does not set those attributes. You can also add a separate route-level limiter for the whole endpoint.

## Why the controller is in the key

Two controllers may both expose `create`. The `exert.action_id` includes the controller class, so those counters stay separate. Without it, a busy operation in one area could consume another area's budget.

If the same controller is mounted on several endpoints and they need separate limits, include a stable route name in your key too.

## Layer two different limits

An endpoint-wide limit can reduce overall load before selection. A per-action limit can give an expensive operation a smaller budget after selection.

For example, reading a report's status and exporting a full report may share a controller but need different limits. Register distinct named limiters rather than placing the same throttle twice and accidentally counting every request twice.

Predictions also run action middleware. Account for form validation traffic when choosing the limit; live validation may send more requests than final submission.

For metadata timing, see [Logging and metadata](./logging).
