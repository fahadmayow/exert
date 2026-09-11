<?php

namespace Exert\Tests\Feature;

use Exert\Action;
use Exert\Tests\Fixtures\ExampleController;
use Exert\Tests\TestCase;
use Illuminate\Foundation\Http\FormRequest;

class ActionPrecognitionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        PrecognitiveAction::$runs = 0;
        ExampleController::$registry['create'] = PrecognitiveAction::class;
    }

    public function test_normal_request_executes_the_action(): void
    {
        $this->postJson('/actions?action=create', [
            'name' => 'Fahad',
            'email' => 'fahad@example.com',
        ])->assertOk()->assertExactJson([
            'name' => 'Fahad',
            'email' => 'fahad@example.com',
        ]);

        $this->assertSame(1, PrecognitiveAction::$runs);
    }

    public function test_valid_prediction_never_executes_the_action(): void
    {
        $this->withHeaders(['Precognition' => 'true'])
            ->postJson('/actions?action=create', [
                'name' => 'Fahad',
                'email' => 'fahad@example.com',
            ])
            ->assertNoContent()
            ->assertHeader('Precognition', 'true')
            ->assertHeader('Precognition-Success', 'true');

        $this->assertSame(0, PrecognitiveAction::$runs);
    }

    public function test_invalid_prediction_returns_validation_errors(): void
    {
        $this->withHeaders(['Precognition' => 'true'])
            ->postJson('/actions?action=create', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'email']);

        $this->assertSame(0, PrecognitiveAction::$runs);
    }

    public function test_prediction_can_validate_only_selected_fields(): void
    {
        $this->withHeaders([
            'Precognition' => 'true',
            'Precognition-Validate-Only' => 'name',
        ])->postJson('/actions?action=create', [
            'name' => 'Fahad',
        ])->assertNoContent()
            ->assertHeader('Precognition-Success', 'true');

        $this->assertSame(0, PrecognitiveAction::$runs);
    }
}

class PrecognitiveAction extends Action
{
    protected array $methods = ['POST'];

    protected bool $precognition = true;

    public static int $runs = 0;

    public function handle(PrecognitiveRequest $request): array
    {
        static::$runs++;

        return $request->validated();
    }
}

class PrecognitiveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string'],
            'email' => ['required', 'email'],
        ];
    }
}
