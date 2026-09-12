---
title: "make:action"
description: "Generate the starting point for an operation."
---

# make:action

```bash
php artisan make:action Orders/CancelOrder
```

By default this creates `app/Http/Actions/Orders/CancelOrder.php`. The stub includes GET methods, enabled Precognition, an empty `middleware()` method, and a placeholder handler. Replace those defaults to suit the operation.

## What to change first

The generated file is a starting point, not a finished endpoint:

1. Set the correct HTTP methods.
2. Choose whether prediction should be enabled.
3. Add an injected form request or other validation.
4. Add permissions where needed.
5. Replace the placeholder handler result.
6. Register the class in a controller map.

An action named `CancelOrder` still starts with GET in the stub. The generator does not infer the correct method from the name.

## Directory and namespace

With the default configuration, `Orders/CancelOrder` produces `app/Http/Actions/Orders/CancelOrder.php` in the corresponding application namespace. `actions_path` changes the default directory for future files.

## Overwriting and test generation

```bash
php artisan make:action Orders/CancelOrder --force
php artisan make:action Orders/CancelOrder --test
```

`--force` replaces an existing file. Laravel's matching-test support also exposes `--pest` and `--phpunit`, using your application's test setup.

To change every future generated action, see [Custom templates](./templates).
