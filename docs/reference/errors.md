---
title: "Error reference"
description: "Know which layer rejected the request."
---

# Error reference

| Condition | Result |
| --- | --- |
| Missing, non-string, or unknown action name | `404` (`NotFoundHttpException`). |
| Selected registry value is not a valid class-string | `LogicException`; normally a server error. |
| Registered class does not exist | `LogicException`; normally a server error. |
| Unsupported HTTP method | `405` (`MethodNotAllowedHttpException`), with `Allow`. |
| Invalid `action_key` or `action_source` configuration | `LogicException`; normally a server error. |
| Registered class does not implement `ActionInterface` | `LogicException`; normally a server error. |
| Action has no public `handle()` | `LogicException`; normally a server error. |
| Form request validation fails | Laravel's validation response; normally `422` for JSON. |
| Form request authorization fails | Normally `403`. |
| Authentication or another middleware rejects the request | That middleware's response. |

Send `Accept: application/json` when you want JSON errors. Exert uses your application's exception handling; it does not define a separate error envelope.

## Read the error in context

A 404 means no applicable action name was found in the registry. Once an entry is selected, an invalid value, missing class, or incompatible class is reported as a configuration error instead.

A 405 can come from the outer Laravel route or from Exert's action method check. The route runs first. See [HTTP methods](/concepts/http-methods) for an example with two different method lists.

Server errors from invalid configuration are developer errors. Correct the key, source, mapping, or handler rather than adding a client retry.

## Application errors stay application errors

Exert does not catch a failed business operation and turn it into success. Laravel's exception handler determines how application exceptions become responses. If your application customizes validation or authorization responses, the resulting JSON may differ from the examples here.

For common setup problems, use [Troubleshooting](./troubleshooting).
