---
layout: home
title: Exert — one operation, one clear home
description: A friendly, practical guide to Exert, the opinionated HTTP action package for Laravel.
hero:
  name: Exert.
  text: One operation. One clear home.
  tagline: Give each HTTP operation a small action class. Keep related actions together. Let Laravel handle the familiar parts.
  image:
    src: /favicon.svg?v=32
    alt: exert
  actions:
    - theme: brand
      text: Build your first endpoint
      link: /start/first-action
    - theme: alt
      text: Why Exert?
      link: /start/what-is-exert
features:
  - title: A name for the work
    details: Approve an order. Send an invitation. Export a report. Each operation gets a class with a clear job.
    link: /concepts/actions
    linkText: Meet actions
  - title: An explicit public menu
    details: Controllers map allowed names to classes. Adding a file does not quietly create a public endpoint.
    link: /concepts/controllers
    linkText: See the controller map
  - title: Laravel, still familiar
    details: Keep form requests, policies, middleware, and response objects. Add action-level input prediction when you need it.
    link: /precognition/overview
    linkText: Explore Precognition
---

## Start small. Build something useful.

Exert is **opinionated and HTTP-oriented**. It groups related actions behind one endpoint, with the action name in the query string by default:

```http
POST /api/orders?action=cancel
Content-Type: application/json

{"order_id":42}
```

The route leads to a controller. The controller selects a registered action. The action handles the HTTP operation. Your application supplies the permissions and business rules.

### Choose your reading path

| If you want to…                                  | Start here                                                                                                  |
| ------------------------------------------------ | ----------------------------------------------------------------------------------------------------------- |
| Decide whether the pattern fits your application | [Meet Exert](/start/what-is-exert)                                                                          |
| Install Exert in your Laravel application              | [Installation](/start/installation)                                                                         |
| Run an example without a database                | [Your first endpoint](/start/first-action)                                                                  |
| Put the pieces together for a real workflow      | [An order workflow](/guides/order-workflow)                                                                 |
| Check a form without executing its operation     | [Meet Precognition](/precognition/overview)                                                                 |
| Find a setting, command, or error                | [Configuration](/reference/configuration) · [Commands](/commands/make-action) · [Errors](/reference/errors) |

