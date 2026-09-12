---
title: "Try a prediction"
description: "Send the headers, inspect the result, then submit for real."
---

# Try a prediction

Use the same URL and method as the real operation, adding the `Precognition` header:

```bash
curl -i -X POST 'http://127.0.0.1:8000/api/system?action=echo' \
  -H 'Accept: application/json' \
  -H 'Content-Type: application/json' \
  -H 'Precognition: true' \
  -d '{"message":"Hello"}'
```

A valid prediction returns `204` with `Precognition-Success: true` and does not call `handle()`. Invalid input returns validation errors, normally `422` for a JSON request. Authorization and middleware checks still apply.

To check selected fields, add a comma-separated list in the header:

```http
Precognition-Validate-Only: message
```

Send the real submission without the `Precognition` header to run the handler.

## Try an invalid message

Using the quick-start application, send an empty body:

```bash
curl -i -X POST 'http://127.0.0.1:8000/api/system?action=echo' \
  -H 'Accept: application/json' \
  -H 'Content-Type: application/json' \
  -H 'Precognition: true' \
  -d '{}'
```

Expect 422 with a validation error for `message`. No handler execution is needed to discover that error.

## Check one part of a larger form

A form with `name` and `email` can send `Precognition-Validate-Only: email` while the user edits the email field. That checks the selected rules, not the full submission contract.

Do not treat a successful partial check as proof that all required fields are ready. The real submission validates again.

## Keep authentication in the request

Predictions need the same session or API credentials as the real operation. A 401 or 403 is an access failure, not a validation result to silently ignore in the frontend.

## Send the real request

Remove the `Precognition` header. For `EchoMessage`, the real submission returns the message with status 200 instead of the empty 204 prediction result.

See [Prediction tests](/testing/precognition) for asserting these differences in your application.
