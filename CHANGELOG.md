# Changelog

## v0.1.0 — 2026-09-12

First stable release of Exert for Laravel 12.51 and later, including Laravel 13.

- Group registered actions behind controller endpoints using `Route::exert($uri, $controller, $methods)` or the fluent `Route::exert($uri)->controller(...)` form.
- Route standalone action classes directly with `Route::exert($uri)->action(...)` and include them in `exert:list` output.
- Define `/` as the action used only when the configured request key is absent; use `*` for unregistered values and as the absent-key fallback when `/` is not registered.
- Validate action registries consistently so invalid definitions fail with clear configuration errors.
- Define allowed HTTP methods and inject handler dependencies and route parameters through Laravel's container.
- Run action middleware with Laravel alias and group resolution, parameters, priority, and response handling.
- Respect route middleware exclusions and Laravel's test middleware-disable flag.
- Return Laravel-compatible responses with standard HTTP errors for unsupported methods and unavailable actions.
- Inspect action metadata and configure per-action rate limits.
- Generate actions and controllers and list registered actions with Artisan commands.
- Test the lowest, latest, and every stable patch release across the supported Laravel range with automated compatibility workflows.

Validation: 156 passing tests with 486 assertions; strict Composer validation passes.
