---
title: "exert:messages"
description: "Create the HTTP status messages configuration file."
---

# exert:messages

```bash
php artisan exert:messages
```

This creates `config/messages.php` from the package's default HTTP status message map. Each entry groups the status text, the numeric code, and a human-readable message:

```php
'404' => [
    'status'  => 'NOT FOUND',
    'code'    => 404,
    'message' => 'The requested resource could not be found.',
],
```

The package ships this map as a configuration template. Run the command (or publish the tag) to add it to your application, then read it with the standard config helper:

```php
$message = config('messages.404.message');
```

Edit the application's copy to adjust wording or add statuses. Exert does not read or overwrite it at runtime.

## Options

```bash
php artisan exert:messages --force
```

An existing `config/messages.php` is protected unless you pass `--force`. Without it the command makes no changes and exits with a failure code.

## Publish instead

The same file can be created with the `exert-messages` publish tag:

```bash
php artisan vendor:publish --tag=exert-messages
```

Add `--force` to replace an existing file. Both approaches copy the identical package default.
