---
title: "Your first endpoint"
description: "Build two actions, validate input, and send your first requests."
---

# Your first endpoint

This example adds a status action and a message action. It needs no database. The message action also supports Precognition so you can try validation without running its handler.

The examples use an application with `routes/api.php` loaded and the usual `/api` prefix. Exert does not enable API routing or create route files for you.

## 1. Generate the classes

Run these commands in your Laravel application:

```bash
php artisan make:action-controller SystemController
php artisan make:action System/Status
php artisan make:action System/EchoMessage
php artisan make:request MessageRequest
```

Replace the generated files with the following code.

## 2. Add the status action

`app/Http/Actions/System/Status.php`:

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

The base action accepts `GET` by default.

## 3. Define the message input

`app/Http/Requests/MessageRequest.php`:

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        // This demo accepts messages from anyone.
        return true;
    }

    public function rules(): array
    {
        return [
            'message' => ['required', 'string', 'max:255'],
        ];
    }
}
```

`authorize()` controls whether the caller may make this request. Replace the demo's `true` with the permission check your application needs, or enforce access through middleware.

## 4. Add the message action

`app/Http/Actions/System/EchoMessage.php`:

```php
<?php

namespace App\Http\Actions\System;

use App\Http\Requests\MessageRequest;
use Exert\Action;

class EchoMessage extends Action
{
    protected array $methods = ['POST'];

    protected bool $precognition = true;

    public function handle(MessageRequest $request): array
    {
        return $request->validated();
    }
}
```

Laravel checks the form request's authorization and validation before calling `handle()`.

## 5. Register the actions

`app/Http/Controllers/SystemController.php`:

```php
<?php

namespace App\Http\Controllers;

use App\Http\Actions\System\EchoMessage;
use App\Http\Actions\System\Status;
use Exert\ActionController;

class SystemController extends ActionController
{
    protected array $actions = [
        'status' => Status::class,
        'echo' => EchoMessage::class,
    ];
}
```

`status` and `echo` are the public names. They do not need to match the PHP class names.

## 6. Register the endpoint

Add this to your application's loaded `routes/api.php`:

```php
use App\Http\Controllers\SystemController;
use Illuminate\Support\Facades\Route;

Route::exert('/system', SystemController::class, ['GET', 'POST'])
    ->name('system.actions');
```

These demo actions are public. Add the authentication and authorization your real endpoints need; see [middleware and access control](/middleware/routes).

## 7. Send requests

Read the status:

```bash
curl -H 'Accept: application/json' \
  'http://127.0.0.1:8000/api/system?action=status'
```

```json
{"status":"ok"}
```

Send a message:

```bash
curl -X POST 'http://127.0.0.1:8000/api/system?action=echo' \
  -H 'Accept: application/json' \
  -H 'Content-Type: application/json' \
  -d '{"message":"Hello"}'
```

```json
{"message":"Hello"}
```

Notice that `action=echo` stays in the query string even for a POST request. The body contains the action's data. A missing message returns a validation error with status `422`.

## What you just built

Two operations now share `/api/system`, but each owns its behavior:

| Request | Selected class | Result |
| --- | --- | --- |
| `GET ?action=status` | `Status` | A small status response. |
| `POST ?action=echo` | `EchoMessage` | The validated message. |
| `POST ?action=status` | `Status` | 405: this action only accepts GET. |
| `GET ?action=missing` | None | 404: the name is not registered. |

You can add another operation without editing either handler. Create the class, add a name to the controller map, and make sure the route allows its method.

The echo example enables Precognition explicitly. We will use it again in [Try a prediction](/precognition/requests).

**Next:** understand [the action class](/concepts/actions) and [the controller map](/concepts/controllers).
