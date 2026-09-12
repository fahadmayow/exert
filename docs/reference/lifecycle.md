---
title: "Request lifecycle"
description: "Follow the request from selection to response."
---

# Request lifecycle

For the supplied base classes, Exert:

1. Reads the configured action key from the configured source.
2. Finds that name in the controller's mapping.
3. Resolves the mapped class through Laravel's container and checks `ActionInterface`.
4. Adds the selected action's identity to request attributes.
5. Checks the allowed HTTP method and that `handle()` is public.
6. Resolves and runs action middleware, applying route exclusions.
7. Resolves handler dependencies. For a prediction, validates them and stops before execution.
8. Runs `handle()` for a normal request and converts its result to an HTTP response.

Exert uses Laravel's HTTP pipeline. Exceptions inside it are handled by Laravel's exception handler, allowing surrounding middleware to process the resulting error response as the pipeline returns.

## What exists at each stage?

| Stage | Useful facts |
| --- | --- |
| Route middleware | Shared user or tenant setup can run. Exert has not selected an action yet. |
| Action construction | Constructor dependencies resolve. Action middleware has not run. |
| Identity assignment | The validated registry name and classes are available as attributes. |
| Method check | Rejection happens before action middleware. |
| Action middleware | Action-specific checks can read the identity. |
| Handler dependency resolution | Form requests authorize and validate. |
| Handler execution | Normal requests perform the operation. Predictions skip this stage. |
| Response returns | Action and route middleware can inspect the response. |

This ordering explains several design choices: shared authentication belongs early, constructors should be lightweight, and per-action counters belong after identity assignment.

See [Internals](./internals) if you are maintaining the package rather than writing application actions.
