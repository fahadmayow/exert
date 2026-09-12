# Exert

An **opinionated, HTTP-oriented Laravel package** for grouping related actions behind one endpoint.

```text
POST /api/orders?action=cancel
    → OrderController
    → CancelOrder
    → HTTP response
```

Each operation gets its own action class. A controller explicitly maps the names clients may call. Laravel handles requests, dependency injection, middleware, validation, and responses. Your application supplies access control and business rules.

## Documentation

The full guide now lives in the **VitePress documentation site** under [`docs/`](docs/index.md):

- [Meet Exert](docs/start/what-is-exert.md) — the idea, the tradeoffs, and when it fits.
- [Installation](docs/start/installation.md) — requirements and Composer installation.
- [Your first endpoint](docs/start/first-action.md) — a complete example with no database.
- [An order workflow](docs/guides/order-workflow.md) — validation, authorization, and a state change.
- [Precognition](docs/precognition/overview.md) — action-based input prediction.
- [Configuration reference](docs/reference/configuration.md) and [troubleshooting](docs/reference/troubleshooting.md).

## Installation

The package requires Laravel `^13.31`. In your Laravel application, run:

```bash
composer require fahadmayow/exert
```

See the [installation guide](docs/start/installation.md) for provider registration and optional configuration publishing.

## Read the site locally

With the project's VitePress dependency installed:

```bash
node node_modules/vitepress/bin/vitepress.js dev docs
```

Build the static documentation:

```bash
node node_modules/vitepress/bin/vitepress.js build docs
```

## Package development

```bash
composer install
composer test
```

See [Package development](docs/contributing/development.md) for more checks and documentation commands.

## License

[MIT](LICENSE).
