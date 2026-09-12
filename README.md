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

Read the complete guide on the [Exert documentation site](https://fahadmayow.github.io/exert/):

- [Meet Exert](https://fahadmayow.github.io/exert/start/what-is-exert) — the idea, the tradeoffs, and when it fits.
- [Installation](https://fahadmayow.github.io/exert/start/installation) — requirements and Composer installation.
- [Your first endpoint](https://fahadmayow.github.io/exert/start/first-action) — a complete example with no database.
- [An order workflow](https://fahadmayow.github.io/exert/guides/order-workflow) — validation, authorization, and a state change.
- [Precognition](https://fahadmayow.github.io/exert/precognition/overview) — action-based input prediction.
- [Configuration reference](https://fahadmayow.github.io/exert/reference/configuration) and [troubleshooting](https://fahadmayow.github.io/exert/reference/troubleshooting).

## Installation

The package requires Laravel 12.51 or later, including Laravel 13. In your Laravel application, run:

```bash
composer require fahadmayow/exert
```

See the [installation guide](https://fahadmayow.github.io/exert/start/installation) for provider registration and optional configuration publishing.

## Package development

```bash
composer install
composer test
```

See [Package development](https://fahadmayow.github.io/exert/contributing/development) for more checks and documentation commands.

## License

[MIT](LICENSE).
