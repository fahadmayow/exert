---
title: "Installation"
description: "Install Exert in your Laravel application with Composer."
---

# Installation

Install Exert with Composer, then use Laravel package discovery to load its provider.

## Requirements

- Laravel 12.51 or newer, including Laravel 13, as declared in `composer.json`.
- PHP 8.2 or newer, within the range supported by your installed Laravel version. Laravel 13 requires PHP 8.3 or newer.
- Composer.

Laravel versions before 12.51 are outside the package's supported range.

## Install with Composer

Run this command from your Laravel application's root directory:

```bash
composer require fahadmayow/exert
```

Composer installs Exert and checks that your application's dependencies meet its requirements.

## Register the provider

Laravel package discovery registers `Exert\ExertServiceProvider` automatically.

If you have disabled discovery for this package, add this class to your application's existing `bootstrap/providers.php` array:

```php
Exert\ExertServiceProvider::class,
```

## Publish the configuration

Publishing is optional; the provider loads the defaults automatically.

```bash
php artisan vendor:publish --tag=exert-config
```

This creates `config/exert.php` in your application.

## Before the first request

You should now have the package available in your application. The package adds the route macro and Artisan commands; it does not add endpoints on its own.

Check the commands without creating a file:

```bash
php artisan help make:action
php artisan help exert:list
```

The examples in this guide use a loaded `routes/api.php` with the usual `/api` prefix. If your application has not enabled API routes, set that up through your application's normal Laravel routing configuration first.

**Next:** [Create your first action](./first-action).
