---
title: "Configuration reference"
description: "The three settings, their defaults, and when they are read."
---

# Configuration reference

Publish the configuration if you need to change the defaults:

```bash
php artisan vendor:publish --tag=exert-config
```

```php
return [
    'action_key' => 'action',
    'action_source' => 'query',
    'actions_path' => 'Http/Actions',
];
```

| Setting | Default | Purpose |
| --- | --- | --- |
| `action_key` | `action` | Request key holding the registered action name. |
| `action_source` | `query` | Read selection from `query`, `body`, or `both`. |
| `actions_path` | `Http/Actions` | Action generator directory relative to `app/`. |

## Selection settings

The key must start with an ASCII letter or underscore and contain only ASCII letters, numbers, and underscores. Dots and brackets are not supported in the key. Public action names may still contain dots or hyphens.

Invalid key or source configuration throws a `LogicException` during resolution. See [Choosing an action](/concepts/selection) for examples and source precedence.

## Generator directory

`actions_path` is relative to the application's `app/` directory:

```php
'actions_path' => 'Domain/Actions',
```

`make:action Orders/CancelOrder` would then create `app/Domain/Actions/Orders/CancelOrder.php`, with namespace `App\Domain\Actions\Orders` in a standard application.

Use a non-empty relative directory made of valid namespace segments. Each segment must start with an ASCII letter or underscore, followed by ASCII letters, numbers, or underscores. Slash and backslash separators are accepted. Do not include an absolute path or `..` segments. Invalid paths throw an `InvalidArgumentException` when the generator uses them.

The generator respects the application's root namespace. Changing this setting affects future generated files; it does not move existing actions or change controller mappings.

## Cached configuration

The provider merges package defaults with your application configuration. After changing a cached configuration, clear or rebuild it as appropriate:

```bash
php artisan config:clear
```

For a deployment that uses cached configuration:

```bash
php artisan config:cache
```
