# Exert

An opinionated Laravel package for grouping small, focused action classes behind a single controller endpoint. Each action defines its own HTTP method, middleware, and handler, while the controller explicitly maps action names to the classes it allows.

```text
GET /api/system?action=status
    → SystemController
    → Status action
    → JSON response
```

The package provides:

- Explicit action registration per controller.
- Flexible `handle()` parameters resolved through Laravel's service container.
- Action-specific middleware, with shared checks handled by Laravel routes.
- Laravel response conversion for arrays, strings, and other supported return values.
- Standard HTTP exceptions for unavailable actions and unsupported methods.
- Artisan generators for actions and action controllers.

## Contents

- [What it solves and why use it](#what-it-solves-and-why-use-it)
- [Requirements](#requirements)
- [Installation](#installation)
- [Quick start](#quick-start)
- [Route macro](#route-macro)
- [Configuration](#configuration)
- [Artisan commands](#artisan-commands)
- [Writing actions](#writing-actions)
- [Middleware](#middleware)
- [Execution and errors](#execution-and-errors)
- [Calling actions directly](#calling-actions-directly)
- [Action visibility](#action-visibility)
- [Testing](#testing)
- [Things to note](#things-to-note)
- [Troubleshooting](#troubleshooting)
- [Package development tests](#package-development-tests)
- [License](#license)

## What it solves and why use it

### Your application does more than CRUD

Create, read, update, and delete are a useful starting point for an API. But imagine an order that also needs to be approved, paid, cancelled, refunded, shipped, or have its invoice resent. Each operation has its own inputs, permissions, validation, and side effects.

REST does not limit an API to four operations; those four describe CRUD. You can model richer workflows with resources, subresources, and additional routes. The practical problem this package addresses is how to organize those workflows when a CRUD-shaped controller no longer communicates what the application actually does.

For example, putting cancellation and shipping behind one `update()` method can lead to branches that inspect fields or status changes to decide which business operation to run. Adding more methods to the same controller makes those operations explicit, but the controller can still grow into a large file with unrelated dependencies and checks.

Exert gives each operation a name and a file, while keeping related operations registered together.

### One operation, one place to work

An order group might look like this:

```text
app/Http/
├── Controllers/
│   └── OrderController.php       # Maps public action names to classes
└── Actions/
    └── Orders/
        ├── CreateOrder.php
        ├── ApproveOrder.php
        ├── CancelOrder.php
        ├── ShipOrder.php
        ├── RefundOrder.php
        └── ResendInvoice.php
```

With the corresponding route and mappings, a client could request:

```http
POST /api/orders?action=resend-invoice
Content-Type: application/json

{"order_id":42}
```

The controller selects `ResendInvoice`; that action reads and validates its input, checks any action-specific permissions, and calls the service responsible for sending the invoice. Someone changing invoice delivery can work in that action without navigating cancellation or shipping code.

### Why use it?

- **Make intent visible.** Names such as `ApproveOrder` and `RefundOrder` describe business operations more precisely than a collection of branches inside `update()`.
- **Keep files focused.** Each handler declares the dependencies it needs and can evolve independently of neighboring actions.
- **Share checks at the right level.** Apply common middleware through Laravel routes or route groups and additional checks to individual actions.
- **Keep registration explicit.** The controller's mapping provides a readable list of the operations the endpoint exposes. Creating a class alone does not make it callable.
- **Keep Laravel's familiar tools.** Actions use the container, request validation, middleware, response conversion, and exception handler.

### When is it a good fit?

Use it for applications with many named workflows: approving expenses, inviting team members, retrying payments, publishing content, exporting reports, or managing order transitions. It is especially useful when your team wants related operations behind a shared endpoint while keeping each implementation in its own file.

A straightforward CRUD resource may be clearer with a normal Laravel resource controller. Laravel's single-action controllers also provide one file per operation when you prefer a separate route for each operation.

This package offers an action-oriented API convention, not an automatic improvement to REST or a performance optimization. Grouping operations behind one route also means route names alone cannot distinguish them: include the action identifier in your API documentation and, where useful, application logs and metrics. Authorization and business rules still belong in your application.

## Requirements

- Laravel `^13.31`, as declared in this package's `composer.json`.
- A PHP version and extensions compatible with the Laravel version Composer installs. The bundled Laravel dependency declares PHP `^8.3`.
- Composer.

Earlier Laravel versions are not included in the package's current dependency constraint.

## Installation

The first stable release is `v0.1.0`. See [the changelog](CHANGELOG.md) for release notes.

Install the package in your Laravel application using Composer:

```bash
composer require fahadmayow/exert:^0.1
```

Laravel's package discovery registers `Exert\ExertServiceProvider`. No manual registration is needed when discovery is enabled.

If you have disabled discovery for this package, add its provider to your application's existing `bootstrap/providers.php` array:

```php
Exert\ExertServiceProvider::class,
```

Publishing configuration is optional. Defaults are loaded by the provider even without a published file.

```bash
php artisan vendor:publish --tag=exert-config
```

This creates `config/exert.php` in your application.

## Quick start

The following example exposes a status action and a POST action through one controller. It uses no database models.

### 1. Generate the classes

```bash
php artisan make:action-controller SystemController
php artisan make:action System/Status
php artisan make:action System/EchoMessage
```

With the default configuration, these commands create:

```text
app/
└── Http/
    ├── Actions/
    │   └── System/
    │       ├── Status.php
    │       └── EchoMessage.php
    └── Controllers/
        └── SystemController.php
```

### 2. Implement the actions

Replace `app/Http/Actions/System/Status.php` with:

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

Actions accept `GET` by default. Override `$methods` to allow one or more methods.

Replace `app/Http/Actions/System/EchoMessage.php` with:

```php
<?php

namespace App\Http\Actions\System;

use Illuminate\Http\Request;
use Exert\Action;

class EchoMessage extends Action
{
    protected array $methods = ['POST'];

    public function handle(Request $request): array
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'max:255'],
        ]);

        return ['message' => $data['message']];
    }
}
```

### 3. Register the actions

Update `app/Http/Controllers/SystemController.php`:

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

The names in this array are the public action identifiers. They do not need to match class names; dotted names such as `system.status` also work. Only registered names can be dispatched.

### 4. Register the route

Add this to your application's loaded `routes/api.php`:

```php
use App\Http\Controllers\SystemController;
use Illuminate\Support\Facades\Route;

Route::exert('/system', SystemController::class, ['GET', 'POST']);
```

The examples assume API routing is enabled and uses the `/api` prefix. The package does not create route files, register endpoints, or enable API routing for you.

The Laravel route must accept every method used by the group's actions. An action's `$methods` cannot enable a method that the outer route rejects.

### 5. Send requests

Replace the base URL with your application's address:

```bash
curl -H 'Accept: application/json' \
  'http://127.0.0.1:8000/api/system?action=status'
```

```json
{ "status": "ok" }
```

With `exert.action_source` set to `'body'` or `'both'`, the action key can also be supplied in a JSON request body:

```bash
curl -X POST 'http://127.0.0.1:8000/api/system' \
  -H 'Accept: application/json' \
  -H 'Content-Type: application/json' \
  -d '{"action":"echo","message":"Hello"}'
```

```json
{ "message": "Hello" }
```

## Route macro

Use `Route::exert()` to register a shared endpoint without repeating the controller's `resolve` method:

```php
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

Route::exert('/order', OrderController::class);
```

The third argument, `$allowedMethod`, accepts `null`, a method string, or an array of methods. When omitted or `null`, the route accepts all standard methods supported by Laravel's `Route::any()`. Each selected action still enforces its own `$methods`.

```php
// Only POST requests can reach this endpoint.
Route::exert('/order', OrderController::class, 'POST');

// Accept GET and POST; method names are case-insensitive.
Route::exert('/order', OrderController::class, ['GET', 'POST']);
```

Choose one registration for an endpoint. The macro returns a normal Laravel route, supporting middleware, names, and route groups:

```php
Route::exert('/order', OrderController::class, ['GET', 'POST'])
    ->middleware('auth')
    ->name('order.actions');
```

Action selection is unchanged: clients still send the configured action key, such as `action=refund`. The macro registers `[OrderController::class, 'resolve']`; it does not create a separate URL for each action or bypass method checks.

Registration works with Laravel's route cache because the route handler is a controller method, not a closure. You can still register routes manually with `Route::match()` or `Route::any()` if preferred.

## Configuration

The published `config/exert.php` contains these defaults:

```php
return [
    'action_key' => 'action',
    'action_source' => 'query',
    'actions_path' => 'Http/Actions',
];
```

| Option         | Purpose                                                                                                    |
| -------------- | ---------------------------------------------------------------------------------------------------------- |
| `action_key`   | Request input key used by `resolve()` to select an action.                                                 |
| `action_source` | Where to read the action key: `query` (default), `body`, or `both`. |
| `actions_path` | Default generator directory relative to the application's `app/` directory; also determines the namespace. |

### Change the request key

```php
'action_key' => 'operation',
```

Clients would then send `?operation=status`. With `action_source` set to `'body'` or `'both'`, they can instead include `"operation": "echo"` in their JSON body.

### Change the action source

```php
'action_source' => 'query',
```

- `query` (default): selects the action only from URL query parameters, such as `?action=status`. An action key in the body is ignored.
- `body`: selects the action only from the request body, supporting JSON and regular form data. An action key in the query string is ignored.
- `both`: uses Laravel's `$request->input()`, preserving the previous behavior, including body precedence when Laravel reads body input and both sources contain the key.

If the selected source has no valid registered action, the resolver returns a 404. This setting only affects action selection; actions can still read other request data normally.

Existing clients that send the action in the body must set `action_source` to `'body'` or `'both'`, or move the action key to the query string.

### Change the generated action directory

```php
'actions_path' => 'Domain/Actions',
```

Running `php artisan make:action Users/CreateUser` now creates:

```text
app/Domain/Actions/Users/CreateUser.php
```

Its namespace is `App\Domain\Actions\Users` for an application with the standard `App\` root namespace. The generator respects the application's configured root namespace.

Use a non-empty directory relative to `app/`, with valid PHP namespace segments. Do not include `app/`, absolute paths, or `..` segments. Changing this setting affects future generation; it does not move existing classes or change registered mappings.

### Existing published configuration

New package defaults are merged with application configuration. If you already published the config, add or change individual keys to customize them; you do not need to overwrite the file.

After changing config locally, clear a previously cached configuration:

```bash
php artisan config:clear
```

If your deployment uses cached configuration, rebuild it after making changes:

```bash
php artisan config:cache
```

## Artisan commands

### Generate an action controller

```bash
php artisan make:action-controller UserController
php artisan make:action-controller Admin/UserController
```

Controllers are generated under `app/Http/Controllers`, extend `Exert\ActionController`, and contain an empty `$actions` mapping with a registration example.

### Generate an action

```bash
php artisan make:action Test
php artisan make:action Users/CreateUser
```

Actions are generated in the configured `actions_path`. The template extends `Exert\Action` and includes:

- A `$methods` array set to `['GET']`.
- An empty `middlewares()` method.
- A public `handle()` method returning a placeholder string to replace with your implementation.

### Options and overwrite protection

Both commands use Laravel's class generator for naming, nested namespaces, directory creation, and protection against overwriting existing files.

```bash
php artisan make:action Users/CreateUser --force
php artisan make:action-controller UserController --force
```

`--force` overwrites the target file. Both commands also expose Laravel's `--test`, `--pest`, and `--phpunit` options for matching test generation, using the application's `make:test` command and test setup.

These are dedicated scaffolds. Controller-specific options such as `--resource`, `--model`, and `--invokable` are not supported by `make:action-controller`.

### Customize the templates

Create either file in your Laravel application to override the corresponding package stub:

```text
stubs/action.stub
stubs/action-controller.stub
```

You can copy the originals from `vendor/fahadmayow/exert/src/Console/stubs/`. Keep the `{{ namespace }}` and `{{ class }}` placeholders for generator substitution. Stub overrides affect newly generated files only.

## Writing actions

### Dependency injection

An action must define a public `handle()` method, but its parameters are flexible:

```php
public function handle(): array
```

```php
public function handle(Request $request): array
```

```php
public function handle(Request $request, ReportService $reports): array
```

Import the types you use and bind service interfaces in Laravel's container where needed. The resolver creates action objects with `app()->make()`, so constructor injection is supported too.

The container does not automatically populate scalar parameters from request input. For example, `handle(string $email)` will not extract an `email` field. Read and validate input from `Request`, or pass explicit data to a separate service.

### Return values

`handle()` can return values supported by Laravel's router, including:

| Result                                              | HTTP behavior                                |
| --------------------------------------------------- | -------------------------------------------- |
| Array                                               | JSON response.                               |
| String                                              | Regular response body.                       |
| Laravel view                                        | Rendered response content.                   |
| JSON, redirect, streamed, or other Symfony response | Prepared as an existing response.            |
| Laravel `Responsable` implementation                | Converted through its `toResponse()` method. |

The package calls `Router::prepareResponse()` inside the pipeline destination, before the result returns through middleware. Actions therefore do not need a `Response` return type, while middleware can expect an HTTP response from `$next($request)`.

### HTTP methods

Each action declares its permitted methods through the `$methods` array:

```php
protected array $methods = ['PUT', 'PATCH'];
```

The default is `['GET']`. Comparison is case-insensitive, and duplicate method names are removed after normalization. A mismatch throws HTTP 405 with every permitted method in the `Allow` header. List methods explicitly; `'*'` is not an allow-all wildcard.

Method checking occurs before action middleware and before `handle()` executes. A `GET` action does not automatically accept `HEAD`; include it explicitly if needed. The outer Laravel route must also allow the requested method.

Use separate actions when different HTTP methods represent different business operations.

## Middleware

Exert manages only action-specific middleware. Use Laravel route middleware for shared checks and Laravel's global middleware configuration for application-wide checks.

### Shared middleware through Laravel routes

Protect every action behind an endpoint by attaching middleware to its route:

```php
Route::exert('/system', SystemController::class, ['GET', 'POST'])
    ->middleware('auth');
```

Use a route group to share checks across multiple action endpoints:

```php
Route::middleware(['auth', 'verified'])->group(function () {
    Route::exert('/orders', OrderController::class);
    Route::exert('/reports', ReportController::class);
});
```

Import the controllers you use and choose middleware appropriate to your application. Laravel handles route aliases, middleware groups, priority sorting, and terminating middleware. Route middleware also protects requests with missing or invalid action names.

### Action-specific middleware

Override `middlewares()` in an action to apply checks needed only for that operation:

```php
protected function middlewares(): array
{
    return [
        'can:export-reports',
        \App\Http\Middleware\EnsureCanExport::class,
    ];
}
```

`EnsureCanExport` represents your own application middleware; it is not supplied by Exert. Define the `export-reports` ability in your application's authorization setup. The base action returns an empty list by default.

Action middleware supports registered Laravel aliases such as `'auth:sanctum'`, middleware groups, fully qualified class names, and closures. Register aliases and groups in your application's normal Laravel middleware configuration. Any guards, abilities, or packages referenced by middleware must also be configured in your application.

```text
Laravel route middleware
    → Action selection and method check
    → Action-specific middleware
        → handle()
        → Laravel response preparation
    ← Action-specific middleware
← Laravel route middleware
```

### Middleware behavior and limitations

- Exert uses Laravel's `Router::resolveMiddleware()` to expand aliases and groups, preserve middleware parameters, apply configured middleware priority, and remove duplicate resolved entries within the action list.
- Route exclusions declared with `withoutMiddleware()` also apply to action middleware, using Laravel's normal exclusion matching for aliases, groups, and classes. Exclusions affect only the current route; calling an action without a route supplies no route exclusions.
- Laravel's test middleware-disable flag also skips action middleware, including closures. Action selection, method checks, handler execution, and response conversion still run.
- Parameterized aliases such as `'auth:sanctum'` and class names such as `SomeMiddleware::class.':parameter'` work through Laravel's resolver.
- Priority sorting and duplicate removal apply within the action list; Exert does not merge it with middleware already running on the route. Avoid registering the same check in both places.
- Exert uses `Illuminate\Routing\Pipeline`, so exceptions inside the action pipeline are reported/rendered by Laravel's bound exception handler. Surrounding middleware can then process the rendered error response after `$next($request)`. Without a bound exception handler, exceptions are rethrown.
- Middleware can return an HTTP response early to skip later middleware and the action. Return an HTTP response for predictable behavior; raw arrays and strings from middleware early returns do not pass through action-result conversion.
- Injected `Request` parameters come from Laravel's current request binding. Middleware should mutate the current request object rather than replace it with another instance.
- Action middleware is not registered with Laravel's terminating-middleware mechanism. Attach middleware requiring `terminate()` to a Laravel route or the HTTP kernel.
- Missing actions and method mismatches are rejected before action middleware executes. Put checks that must cover the entire endpoint on its route.

## Execution and errors

For the supplied base classes, dispatch follows these steps:

1. Read the configured action key and find its entry in the controller's `$actions` mapping.
2. Confirm the mapped class exists and resolve it through the container.
3. Confirm it implements `ActionInterface`.
4. Check the action's allowed HTTP methods and public `handle()` method.
5. Resolve the selected action's middleware through Laravel, applying the current route's exclusions, and execute it in the HTTP routing pipeline. Skip middleware when Laravel's middleware-disable flag is `true`.
6. Invoke `handle()` through the container and prepare its result as an HTTP response.

| Condition                                             | Result                                                               |
| ----------------------------------------------------- | -------------------------------------------------------------------- |
| Missing, non-string, or unknown action identifier     | `NotFoundHttpException` with HTTP 404.                               |
| Registered action class does not exist                | `NotFoundHttpException` with HTTP 404.                               |
| Unsupported HTTP method                               | `MethodNotAllowedHttpException` with HTTP 405 and an `Allow` header. |
| Registered class does not implement `ActionInterface` | `LogicException`.                                                    |
| Action has no public `handle()` method                | `LogicException`.                                                    |

Laravel's exception handler renders errors. The package does not impose a custom JSON error shape. Send `Accept: application/json` when a client expects Laravel's JSON error rendering. Application exceptions, including validation failures, propagate to Laravel's handler too.

## Calling actions directly

Inside a Laravel HTTP request, you can invoke an action without an action controller:

```php
$response = app(\App\Http\Actions\System\Status::class)
    ->initiate(request());
```

This applies method validation, action-specific middleware, dependency injection, and response conversion. Direct calls do not execute route middleware. Run shared checks through the route when they are required; `initiate()` accepts only the request.

Calling `handle()` yourself bypasses this lifecycle. For logic reused in jobs or CLI commands, put the reusable work in a service that accepts explicit data, and call it from the HTTP action.

`ActionInterface` defines only this entry point:

```php
public function initiate(Request $request): mixed;
```

It intentionally does not declare `handle()`, allowing different parameter lists. Extend the base `Action` to inherit the documented pipeline. A class implementing the interface directly is responsible for its own execution behavior.

## Action visibility

Laravel's `route:list` and route names continue to describe shared endpoints. Exert provides action metadata alongside them, without creating synthetic routes or changing route names during requests.

### List registered actions

```bash
php artisan exert:list
php artisan exert:list --json
```

The table shows the domain, endpoint, registered action name, effective HTTP methods, class, and declared route/action middleware. Effective methods are the intersection of the action's methods and the outer route's methods. An empty intersection is shown as `None (route blocked)`.

JSON output also includes route names, controller classes, action identifiers, and separate route/action method lists. This can feed documentation or inspection tools; Exert does not generate an OpenAPI document automatically. Middleware aliases are displayed as declared, rather than expanded.

The command finds routes targeting an Exert controller's `resolve()` method, whether registered with `Route::exert()` or manually, including cached controller routes. It resolves controllers and actions through the container and reads their metadata; it never calls `initiate()`, `handle()`, or executes middleware. Keep constructors and metadata methods free of request-dependent side effects so they can run in the console.

Public inspection methods are `ActionController::getActions()`, `Action::getAllowedMethods()`, and `Action::getActionMiddleware()`. Custom classes implementing only `ActionInterface` are listed, but their action methods and middleware are reported as unknown (`null` in JSON).

### Request metadata for logging and monitoring

After validating a registered action, the resolver sets these request attributes before invoking `initiate()`:

| Attribute            | Value                                                |
| -------------------- | ---------------------------------------------------- |
| `exert.action`       | Registered action name, such as `refund`.            |
| `exert.action_class` | Registered action class.                             |
| `exert.controller`   | Controller class selecting the action.               |
| `exert.action_id`    | Controller class plus `::` plus the registered name. |

The controller-qualified identifier distinguishes `create` in different groups. If a controller is mounted on multiple routes, combine it with the route name or URI template to distinguish endpoints. Avoid raw URLs containing IDs as metric labels.

Read attributes rather than client-supplied input:

```php
$actionId = $request->attributes->get('exert.action_id');
```

Action middleware can read these immediately. Route middleware can read them after `$next($request)` returns, and route-level terminating middleware can read them later. They are not available before action selection or if route middleware rejects the request first. Unknown actions do not receive action metadata. Method mismatches retain the validated action identity for error reporting. Direct calls to `initiate()` bypass the resolver and do not populate this metadata.

Exert does not emit lifecycle events or automatically export logs, timings, or metrics. These attributes provide integration points for your application's monitoring tools.

### Per-action rate limiting

Register a named limiter in your application's service provider `boot()` method, using Laravel's `RateLimiter` facade and `Limit` class:

```php
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

RateLimiter::for('exert-operation', function (Request $request) {
    $actionId = $request->attributes->get('exert.action_id');
    abort_if($actionId === null, 500, 'Action identity is required for this limiter.');

    $userId = $request->user()?->getAuthIdentifier();
    $actor = $userId !== null ? 'user:'.$userId : 'ip:'.$request->ip();

    return Limit::perMinute(30)->by($actionId.'|'.$actor);
});
```

Apply the limiter inside the action, where the resolver has already set the metadata:

```php
protected function middlewares(): array
{
    return ['throttle:exert-operation'];
}
```

Each actor then gets a separate counter for each controller/action pair. Put shared authentication on the route so it runs before the action limiter. A limiter using action metadata should not run as outer route middleware, because selection has not happened at that point. You can still use a separate endpoint-wide limiter on the route.

## Testing

Test the controller endpoint to cover selection, method checks, middleware, validation, and response conversion together. With the quick-start routes loaded and no extra authentication middleware, this PHPUnit test can be placed in `tests/Feature/SystemActionsTest.php`:

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;

class SystemActionsTest extends TestCase
{
    public function test_status_action_returns_json(): void
    {
        $this->getJson('/api/system?action=status')
            ->assertOk()
            ->assertExactJson(['status' => 'ok']);
    }

    public function test_echo_action_validates_and_returns_input(): void
    {
        $this->postJson('/api/system?action=echo', [
            'message' => 'Hello',
        ])->assertOk()->assertExactJson(['message' => 'Hello']);

        $this->postJson('/api/system?action=echo', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('message');
    }

    public function test_unknown_action_returns_404(): void
    {
        $this->getJson('/api/system?action=missing')->assertNotFound();
    }

    public function test_action_rejects_an_unsupported_method(): void
    {
        $this->postJson('/api/system', ['action' => 'status'])
            ->assertStatus(405)
            ->assertHeader('Allow', 'GET');
    }
}
```

Run it with:

```bash
php artisan test --filter=SystemActionsTest
```

Add tests for your middleware's authorization rules and early responses. When manually constructing a request for an isolated action test, bind that same request in the application container if `handle()` injects `Request`.

Use Laravel's test helper to disable all middleware, including action middleware:

```php
$this->withoutMiddleware();

$this->getJson('/api/system?action=status')->assertOk();
```

Exert skips action middleware when the container's `middleware.disable` binding is exactly `true`. An absent or `false` binding keeps middleware enabled.

To exclude particular middleware on an endpoint, use the route's `withoutMiddleware()` method. This also excludes matching entries declared by its actions:

```php
Route::exert('/reports', ReportController::class)
    ->withoutMiddleware(\App\Http\Middleware\EnsureCanExport::class);
```

`ReportController` represents your application's action controller. Route exclusions do not remove global middleware.

## Things to note

- **Routes remain explicit.** The generators do not register routes or add action classes to a controller's mapping.
- **The action key is input, not authorization.** Register permitted names and implement the authentication and authorization your endpoint needs.
- **The package is HTTP-oriented.** Actions have request-method checks and HTTP middleware even though their handler parameters are flexible.
- **Route binding does not become handler argument binding.** Arbitrary action handler parameters do not receive the normal controller route-parameter mapping. Read route values from `Request` or explicitly resolve the data you need.
- **Existing Laravel middleware still applies.** Routes in the `web` middleware group retain session and CSRF behavior. Action dispatch does not bypass those checks.
- **Middleware has a cost.** Avoid registering the same expensive check on the route and again in an action's middleware list unless repeated execution is intended.
- **Measure performance in your environment.** Container dispatch, reflection, the extra pipeline, and response preparation add overhead. Compare equivalent endpoints with identical work and middleware. A sleep-based simulated query measures waiting and server queuing, not real database performance; use multiple PHP workers for meaningful concurrency testing.

## Troubleshooting

| Symptom                                              | Check                                                                                                                                                  |
| ---------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------ |
| `make:action` or `make:action-controller` is missing | Confirm Composer installed the package and its provider is discovered or registered. Run `php artisan package:discover` if discovery needs refreshing. |
| Action returns 404                                   | Verify the configured input key, the controller's mapping, and the mapped class's namespace/autoloading.                                               |
| Action returns 405                                   | Check both the outer route's accepted methods and the action's `$methods`.                                                                             |
| Middleware alias cannot be resolved                  | Confirm the alias/group is registered in Laravel and any referenced middleware package or guard is installed and configured.                           |
| New actions appear in an unexpected folder           | Check `exert.actions_path`, cached configuration, and whether an existing published config overrides the default.                                      |
| Handler dependencies cannot be resolved              | Bind service interfaces and avoid expecting scalar parameters to be filled from request input.                                                         |
| Typed middleware receives an invalid return value    | Check whether an inner middleware returns a raw value early; it should return an HTTP response.                                                        |
| POST requests receive a CSRF error                   | Check the route's middleware group and send the CSRF credentials required by your application's web routes.                                            |

## License

MIT, as declared in `composer.json`.

## Package development tests

Install development dependencies and run the committed PHPUnit suite from the package root:

```bash
composer install
composer test
composer test -- --filter=ActionMiddlewareTest
```

The suite uses Orchestra Testbench 11 to boot Laravel 13 with Exert's service provider. It covers HTTP dispatch, the default `action` key and custom keys, response conversion, middleware, metadata, rate limiting, route caching, action listing, and generators. Generator tests use temporary directories and clean up after themselves. No separate Laravel app or database server is required.

GitHub Actions runs the suite on pushes and pull requests with PHP 8.3, 8.4, and 8.5, resolving compatible dependencies for each version. Local tests run against the installed lockfile dependencies.
