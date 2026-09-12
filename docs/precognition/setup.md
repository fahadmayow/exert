---
title: "Enable Precognition"
description: "Use an action property and a form request."
---

# Enable Precognition

Set the property and inject a `FormRequest`:

```php
protected bool $precognition = true;

public function handle(MessageRequest $request): array
{
    return $request->validated();
}
```

The quick-start `EchoMessage` action already does this.

**Do not add `HandlePrecognitiveRequests` to the outer route or route group for an Exert endpoint.** Laravel can stop at the resolver and report success before the selected action is validated. Exert adds the middleware inside the action when the property is enabled.

The base `Action` defaults to `$precognition = false`. The `make:action` template explicitly sets it to `true`. Set it to `false` in generated actions that do not need prediction.

## A small setup checklist

1. The outer route accepts the operation's method.
2. The action accepts that method too.
3. The action sets `$precognition = true`.
4. The handler injects a form request containing its input rules.
5. Action middleware is enabled and has not excluded the Precognition middleware.

The [first endpoint](/start/first-action) already includes a complete `MessageRequest` and `EchoMessage` action with this setup.

## Keep the same input contract

The normal submission and prediction use the same URL, action name, and HTTP method. Do not add a second validation action that slowly develops different rules.

You can still use Laravel's precognitive form-request features when a rule needs different treatment during prediction. The final submission remains responsible for the complete input and business checks.

**Next:** [Try it over HTTP](./requests).
