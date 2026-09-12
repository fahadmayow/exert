---
title: "Meet Precognition"
description: "Check a form before submitting the operation."
---

# Meet Precognition

Imagine a user typing a message. You want to tell them that it is too long before they press Send. You also want the real backend rules to be the source of that answer.

Precognition sends a validation request to the same operation. Exert resolves the handler's dependencies, including its form request, then stops before the handler runs.

```text
Normal request:     middleware → FormRequest → handle() → response
Prediction request: middleware → FormRequest → 204 or validation errors
```

## It is a preview of validation, not a trial run

A prediction does not call the handler and roll back its changes. It never enters the handler. That is why validation must live in an injected `FormRequest`, rather than only inside `handle()`.

Constructors and middleware still run. Keep those steps free of unwanted writes; skipping the handler does not undo earlier work.

## Exert supports action-based Precognition

Enable it using `$precognition = true` on the action. Do not put Laravel's `HandlePrecognitiveRequests` on the outer Exert route or group. That can stop at the resolver before the action's validation is reached.

The base action leaves prediction disabled. Generated actions explicitly enable it. Review that setting when you generate a class.

## A prediction is not a promise

A valid prediction means the checked input passed at that moment. It does not reserve a record, lock stock, or grant permission for a later request. The real submission must still validate, authorize, and enforce current business rules.

Start with [Setup](./setup), then [send a prediction](./requests). Read [What executes](./execution) before adding services with side effects.
