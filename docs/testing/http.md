---
title: "Test the endpoint"
description: "Exercise the same path your clients use."
---

# Test the endpoint

Test through the endpoint to cover selection, methods, middleware, validation, and responses together. With the quick-start example installed:

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;

class SystemActionsTest extends TestCase
{
    public function test_status(): void
    {
        $this->getJson('/api/system?action=status')
            ->assertOk()
            ->assertExactJson(['status' => 'ok']);
    }

    public function test_message_input(): void
    {
        $this->postJson('/api/system?action=echo', ['message' => 'Hello'])
            ->assertOk()
            ->assertExactJson(['message' => 'Hello']);

        $this->postJson('/api/system?action=echo', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('message');
    }

    public function test_unknown_action_and_wrong_method(): void
    {
        $this->getJson('/api/system?action=missing')->assertNotFound();

        $this->postJson('/api/system?action=status', [])
            ->assertStatus(405)
            ->assertHeader('Allow', 'GET');
    }

    public function test_message_prediction(): void
    {
        $this->postJson('/api/system?action=echo', ['message' => 'Hello'], [
            'Precognition' => 'true',
        ])->assertNoContent()
            ->assertHeader('Precognition-Success', 'true');
    }
}
```

Run application tests using your normal Laravel test command:

```bash
php artisan test --filter=SystemActionsTest
```

For real actions, also test denied access, validation failures, and the expected side effects. Assert that prediction does not perform writes or call services used for the final operation.

Laravel's `$this->withoutMiddleware()` also disables Exert action middleware. Exert skips it only when the container's `middleware.disable` binding is exactly `true`. Method checks and dispatch still run. Keep middleware enabled in tests that verify permissions or Precognition.

When manually building a request for an isolated action test, bind that same object as the application's current request if the handler injects a request or form request.

## Make failures part of the contract

For each protected action, test at least one caller who may perform it and one who may not. Test invalid input and a wrong HTTP method too.

A happy-path test that disables all middleware proves much less about the real endpoint. Keep tests that verify permissions close to the action tests so changes to route groups or exclusions are visible.

For operations that modify data, assert the final state, not only the status code. For notifications or external calls, use the appropriate Laravel fakes or service mocks and verify the intended calls.

**Next:** [Test predictions](./precognition), including that no final operation occurs.
