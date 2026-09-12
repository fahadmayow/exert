---
title: "Action classes"
description: "Give one HTTP operation one clear home."
---

# Action classes

An action is a PHP class that describes an HTTP operation. Start with `Exert\Action`, then add a public `handle()` method.

```php
<?php

namespace App\Http\Actions\System;

use Exert\Action;

class Status extends Action
{
    public function handle(): array
    {
        return ['status' => 'ok'];
    }
}
```

This is a complete action. It inherits GET as its allowed method, no action middleware, and disabled Precognition.

## The four parts you can define

| Part | Purpose | Base default |
| --- | --- | --- |
| `$methods` | Allowed HTTP methods | `['GET']` |
| `middleware()` | Checks around this action | An empty array |
| `$precognition` | Enable action-level prediction | `false` |
| `handle()` | Perform the operation and return its result | You must define it |

The handler may take a request, a form request, or services. It may return an array, a response, or another value Laravel can turn into a response. You do not need a fixed handler signature.

::: tip Generated actions have one different default
`make:action` explicitly writes `$precognition = true` in its template. That is different from the base class. Choose the value you need rather than assuming the generated file has no behavior to review.
:::

## Name the work

Names such as `CancelOrder`, `SendInvitation`, and `PublishArticle` describe a specific operation. A name such as `ManageOrder` usually leaves too much work for one class.

Keep the HTTP concerns here: input, permissions, calling services, and choosing the response. Move business logic to a service when it grows or must be shared with a job or command.

## A class is not an endpoint yet

Exert does not scan your action directory and expose everything it finds. The controller's map decides which names are available. The route decides where the controller is reachable.

That explicit chain is useful when you need to answer: “Can a client call this operation?”

**Continue with:** [Controllers and registration](./controllers), [HTTP methods](./http-methods), or [Dependency injection](./dependencies).
