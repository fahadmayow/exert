---
title: "Meet Exert"
description: "A home for each operation, and one place to see what an endpoint allows."
---

# Meet Exert

An order starts small. First you create it. Then someone asks for approval, cancellation, refunds, shipping, and a way to resend the invoice.

Soon, the interesting part of your application is the work it does **between** creating and deleting a record.

Exert gives each of those operations a home: a small action class. A controller keeps the list of operations that clients may call.

```text
POST /api/order?action=cancel
             │
             ▼
      OrderController
       'cancel' → CancelOrder
             │
             ▼
      permission + input checks
             │
             ▼
       CancelOrder::handle()
             │
             ▼
        HTTP response
```

## The problem it tries to solve

When many business operations live in one controller, changing one can mean navigating unrelated validation, permission checks, and dependencies. A large `update()` method can become a switchboard for every possible operation.

With Exert, cancellation code lives in `CancelOrder`. Refund code lives in `RefundOrder`. You can open one file and understand one operation.

```text
app/Http/
├── Controllers/OrderController.php
└── Actions/Order/
    ├── ApproveOrder.php
    ├── CancelOrder.php
    ├── RefundOrder.php
    └── ShipOrder.php
```

## Opinionated, on purpose

Exert makes a few choices for you:

| Choice                                     | What it means in practice                                                                                      |
| ------------------------------------------ | -------------------------------------------------------------------------------------------------------------- |
| Related actions share an endpoint          | `/order?action=cancel` and `/order?action=refund` can use the same controller.                                 |
| Registration is explicit                   | A new class stays private until you add it to a controller's action map and expose that controller on a route. |
| Selection uses the query string by default | Even a POST request sends `?action=cancel`; its body holds the operation's data.                               |
| Each action is an HTTP operation           | It has methods, middleware, and an HTTP result.                                                                |
| Laravel does the familiar work             | Use its container, form requests, authentication, policies, and responses.                                     |

This is a convention for organizing code. It is not a promise that fewer routes make an application faster, and REST is not limited to CRUD. You can model the same workflows with separate resource routes.

## Is it a good fit?

**Consider Exert** when your application has many named operations and you or your team likes keeping related operations together. Examples include approving expenses, inviting team members, publishing content, and processing order transitions.

**Keep a simpler controller** when a resource only needs a few CRUD operations. Laravel's single-action controllers are also a good fit when you prefer one route for each operation.

The choice is about how you find, read, and change code.

## HTTP is the boundary

Exert is HTTP-oriented. An action's lifecycle expects a request, checks its method, and produces a response. It is not a queue job or a general command bus.

If a job and an HTTP endpoint both need to cancel an order, put the shared work in a service. The HTTP action handles the request and calls that service. The job calls the same service with its own input.

::: info Your application still owns access and business rules
A registered action is available for dispatch. Registration alone does not authorize a user, validate an amount, or make a database change safe. Those rules belong in your application.
:::

Ready to see the shape of it? [Install Exert](./installation), then [build your first endpoint](./first-action). The first example needs no database.
