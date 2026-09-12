---
title: "make:action-controller"
description: "Create the map for a group of operations."
---

# make:action-controller

```bash
php artisan make:action-controller OrderController
php artisan make:action-controller Admin/OrderController
```

Controllers go under `app/Http/Controllers`, extend `ActionController`, and start with an empty action mapping. Generators do not register routes or add actions to controllers.

## What the generated class contains

The controller extends `Exert\ActionController` and has an empty `$actions` array with a registration comment. Add public action names and import their classes.

The command does not create actions or register a route. Those explicit steps let you decide what becomes available to clients.

## Options

```bash
php artisan make:action-controller OrderController --force
php artisan make:action-controller OrderController --test
```

Existing files are protected unless you pass `--force`. Matching-test generation also supports Laravel's `--pest` and `--phpunit` options with the application's test setup.

This is an action-controller scaffold, not Laravel's resource-controller generator. Options such as `--resource`, `--model`, and `--invokable` are not supported.

For a full registration example, see [Controllers](/concepts/controllers).
