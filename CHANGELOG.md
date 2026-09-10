# Changelog

## v0.1.0 — 2026-09-10

First stable release of Exert for Laravel `^13.31`.

- Group actions behind controller endpoints with explicit action registration and the `Route::exert()` macro.
- Define allowed HTTP methods and inject handler dependencies through Laravel's container.
- Run action middleware with Laravel alias and group resolution, parameters, priority, and response handling.
- Respect route middleware exclusions and Laravel's test middleware-disable flag.
- Return Laravel-compatible responses with standard HTTP errors for unsupported methods and unavailable actions.
- Inspect action metadata and configure per-action rate limits.
- Generate actions and controllers and list registered actions with Artisan commands.

Validation: 30 passing tests with 109 assertions; strict Composer validation passes.
