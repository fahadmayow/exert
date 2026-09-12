---
title: "Test predictions"
description: "Prove that validation runs and the final operation does not."
---

# Test predictions

A 204 response is useful, but it is not the whole test. You also want to know that the prediction used the expected rules and did not perform the real operation.

## Start with the echo example

These are method excerpts for an application feature test using the [first endpoint](/start/first-action):

```php
public function test_invalid_prediction(): void
{
    $this->postJson('/api/system?action=echo', [], [
        'Precognition' => 'true',
    ])->assertUnprocessable()->assertJsonValidationErrors('message');
}
```

```php
public function test_valid_prediction(): void
{
    $this->postJson('/api/system?action=echo', ['message' => 'Hello'], [
        'Precognition' => 'true',
    ])->assertNoContent()->assertHeader('Precognition-Success', 'true');
}
```

## Verify a real operation is not called

For an application action that calls a service method named `send()` from `handle()`, mock that service during the prediction test:

```php
$this->mock(\App\Services\SendMessageService::class, function ($mock) {
    $mock->shouldNotReceive('send');
});
```

This is a pattern for an application service you define; the echo example itself does not send messages. Send a valid prediction afterward and assert 204. Then write a separate normal-request test that expects `send()` once.

If the action writes to a database, assert that prediction leaves the relevant records unchanged.

## Cover the boundaries

| Case | What to verify |
| --- | --- |
| Invalid input | Field errors are returned; no handler side effect occurs. |
| Valid input | 204 with the success header; no handler side effect occurs. |
| Unauthorized caller | Access is denied, including during prediction. |
| Selected-field validation | Only the requested validation subset is being tested. |
| Normal submission after prediction | The actual operation still executes normally. |
| Contextual form request | The replacement's validation and authorization run in both flows. |

Do not call `withoutMiddleware()` in these tests. The action middleware is what puts the request into prediction mode.
