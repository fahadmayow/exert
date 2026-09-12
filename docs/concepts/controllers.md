---
title: "Controllers and registration"
description: "Keep the public list of operations easy to read."
---

# Controllers and registration

An Exert controller is the menu of operations available through an endpoint. Its main job is mapping public names to action classes.

```php
<?php

namespace App\Http\Controllers;

use App\Http\Actions\System\EchoMessage;
use App\Http\Actions\System\Status;
use Exert\ActionController;

class SystemController extends ActionController
{
    protected array $actions = [
        '/' => Status::class,
        'status' => Status::class,
        'echo' => EchoMessage::class,
        '*' => Status::class,
    ];
}
```

The classes above are defined in [Your first endpoint](/start/first-action).

The `/` entry is optional and handles requests where the configured action key is completely absent. It cannot be selected by sending `/` as the value, and a present empty value does not select it. The `*` entry is optional and handles invalid or unregistered values. It also handles a missing key when `/` is not registered. Without an applicable entry, those requests return 404.

## Names are part of your API

A client sends `?action=echo`. It does not send the PHP class name. You can reorganize your PHP namespaces while keeping the public name stable.

The name must match a registered entry exactly. Dotted names such as `system.status` and hyphenated names such as `send-message` are allowed.

Choose names that make sense to the caller. A small group may only need `cancel`; a larger group may benefit from `orders.cancel`.

## What the resolver does

The route calls the inherited `resolve()` method. It reads the selected name, checks the map, creates the mapped class through Laravel's container, and calls its `initiate()` method.

Unknown names return 404 unless a `*` fallback is registered. A missing selection uses `/` when available, falls back to `*`, and otherwise returns 404. User input is never used directly as a PHP class name.

::: warning Registration is not a permission check
Putting `refund` in the map means it can be selected. Your route middleware, action middleware, form request, or policy must still decide who may refund which order.
:::

## Keep the map useful

Group actions that share a meaningful area of the application. One controller for every operation in the entire application becomes difficult to understand. A controller for orders and another for reports usually tells a clearer story.

You can inspect a controller's map through `getActions()`. The [action-list command](/commands/list-actions) reads this map too, so keep custom map-building logic safe to run in the console.

**Next:** [Register the route](./routes).
