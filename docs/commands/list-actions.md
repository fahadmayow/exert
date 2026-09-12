---
title: "exert:list"
description: "See the operations behind shared routes."
---

# exert:list

```bash
php artisan exert:list
php artisan exert:list --json
```

The command finds registered Exert resolver routes, including manually registered and cached routes. It shows the domain, URI, action name, class, shared HTTP methods, and declared middleware. No shared methods is shown as `None (route blocked)`.

JSON also includes route names, controller classes, action IDs, and separate route/action method lists. Custom implementations of `ActionInterface` that do not extend `Action` have unknown action methods and middleware, shown as `null` in JSON.

The command resolves controllers and actions through the container. It reads their metadata but does not call handlers, `initiate()`, or middleware. Keep constructors and metadata methods safe to call from the console.

**The middleware columns show declarations, not a full access-control report.** Aliases are not expanded, exclusions are not reflected in those lists, and global middleware is not included. Laravel's `route:list` still describes the shared routes.

Public inspection methods are `ActionController::getActions()`, `Action::getAllowedMethods()`, and `Action::getActionMiddleware()`.

## Read the output as a map

The command answers “What did this application register?” It does not call handlers to prove they work, and it does not execute middleware to prove access rules are correct.

An action with `None (route blocked)` has no method in common with its outer route. Check both declarations before debugging the handler.

## JSON fields

| Field group | Fields |
| --- | --- |
| Endpoint | `domain`, `uri`, `route_name`, `controller` |
| Operation | `action`, `action_id`, `class` |
| Methods | `route_methods`, `action_methods`, `effective_methods` |
| Declared middleware | `route_middleware`, `action_middleware` |

Use the JSON output for internal documentation or inspection tools. Exert does not generate an OpenAPI document automatically.

Constructors and custom metadata methods must be safe in console execution. Request-dependent work there can make listing fail before any handler is involved.
