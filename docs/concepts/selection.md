---
title: "Choosing an action"
description: "Understand the key, the value, and where Exert reads them."
---

# Choosing an action

In `?action=orders.cancel`:

- `action` is the input key, configured by `action_key`.
- `orders.cancel` is the action name, registered in the controller.

The input key must start with an ASCII letter or underscore. The rest may contain ASCII letters, numbers, and underscores. For example, `operation` and `_action` are valid; `operation.name`, `action[]`, and `1action` are not. Invalid key configuration throws a `LogicException`.

Action names may contain dots or hyphens. They must match a registered name exactly. The resolver accepts only string values. By default, missing, unknown, or non-string selections return `404`.

## Default and fallback actions

Controllers may register two reserved action names:

| Name | When it is selected |
| --- | --- |
| `/` | The configured input key is absent from the configured source. |
| `*` | The input key is present but invalid or unregistered, or the key is absent and `/` is not registered. |

An exact registered action always takes precedence, except that `/` cannot be selected by a request value. When the key is completely absent, the resolver tries `/` first and then `*`. A present empty, `null`, `/`, array, or otherwise invalid value selects `*`, not `/`. If neither applicable reserved action is registered, the resolver returns `404` as usual.

The distinction follows `action_source`. For example, with `query`, a body-only `action` value does not make the query key present, so `/` is selected when it is registered.

To change the key:

```php
'action_key' => 'operation',
```

The default source would then expect `?operation=echo`.

## Query, body, or both?

| `action_source` | Where Exert reads the action name |
| --- | --- |
| `query` | URL query string only. This is the default. Body values are ignored for selection. |
| `body` | JSON body or regular form body only. Query values are ignored for selection. |
| `both` | Laravel's `$request->input()`, including its normal body/query precedence. |

For example, with `body` enabled:

```bash
curl -X POST 'http://127.0.0.1:8000/api/system' \
  -H 'Accept: application/json' \
  -H 'Content-Type: application/json' \
  -d '{"action":"echo","message":"Hello"}'
```

With `both`, a body value can override the query value when Laravel reads body input. This includes JSON input. Prefer one source when possible so callers and middleware agree on the selected operation.

This setting only changes action selection. A query-selected action can still read and validate a request body normally. Any source other than `query`, `body`, or `both` throws a `LogicException`.

## Read one request carefully

```http
POST /api/system?action=echo
Content-Type: application/json

{"message":"Hello", "action":"status"}
```

With the default `query` source, this selects `echo`. The body's `action` value is not used for selection. With `body` or `both`, the same JSON request selects `status`, which then fails the POST method check in our example.

This is why choosing one source makes client behavior easier to explain.

::: warning A signed URL does not sign the body
Use query selection when a URL signature grants access to an action. Read [Signed URLs](/guides/signed-urls) before combining these features.
:::

## Use metadata after selection

Middleware that needs the selected operation should read Exert's request attributes after selection. Re-reading `$request->input('action')` can produce a different value from the selected query value.

```php
$selected = $request->attributes->get('exert.action');
```

For a default or fallback dispatch, this attribute contains `/` or `*`, and `exert.action_id` uses the same resolved name.

See [Logging and metadata](/guides/logging) for when these attributes become available.
