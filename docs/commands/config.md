---
title: "exert:config"
description: "Create the Exert configuration file."
---

# exert:config

```bash
php artisan exert:config
```

This creates `config/exert.php` from the package's default configuration:

```php
return [
    'action_key' => 'action',
    'action_source' => 'query',
    'actions_path' => 'Http/Actions',
];
```

The provider merges these same defaults at registration, so the file is optional. Create it when you want an application copy to edit.

## Options

```bash
php artisan exert:config --force
```

An existing `config/exert.php` is protected unless you pass `--force`. Without it the command makes no changes and exits with a failure code.

## Publish instead

The same file can be created with the `exert-config` publish tag:

```bash
php artisan vendor:publish --tag=exert-config
```

Add `--force` to replace an existing file. Both approaches copy the identical package default. See the [configuration reference](/reference/configuration) for what each setting does.
