<?php

namespace Exert\Tests\Feature;

use Closure;
use Exert\Action;
use Exert\ActionDispatcher;
use Exert\Tests\Fixtures\ExampleController;
use Exert\Tests\TestCase;
use Illuminate\Container\Container;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ActionContextualBindingTest extends TestCase
{
    public static function substitutedRequests(): array
    {
        return [
            'normal valid' => [false, true, true],
            'prediction valid' => [true, true, true],
            'normal invalid' => [false, true, false],
            'prediction invalid' => [true, true, false],
            'normal unauthorized' => [false, false, true],
            'prediction unauthorized' => [true, false, true],
        ];
    }

    #[DataProvider('substitutedRequests')]
    public function test_contextual_form_request_substitution(
        bool $precognitive,
        bool $authorized,
        bool $valid,
    ): void {
        ExampleController::$registry['contextual'] = ContextualAction::class;
        $action = new ContextualAction();
        $this->app->instance(ContextualAction::class, $action);
        $this->app->when(ContextualAction::class)
            ->needs(ContextualRequest::class)
            ->give(SubstitutedContextualRequest::class);

        // The original request requires name and always authorizes.
        // The replacement requires email and checks the authorization input.
        $response = $this->postJson('/actions?action=contextual', [
            'allowed' => $authorized,
            'email' => $valid ? 'fahad@example.com' : 'invalid',
        ], $precognitive ? ['Precognition' => 'true'] : []);

        if (! $authorized) {
            $response->assertForbidden();
        } elseif (! $valid) {
            $response->assertUnprocessable()
                ->assertJsonValidationErrors(['email'])
                ->assertJsonMissingValidationErrors(['name']);
        } elseif ($precognitive) {
            $response->assertNoContent()
                ->assertHeader('Precognition-Success', 'true');
        } else {
            $response->assertOk()->assertExactJson([
                'email' => 'fahad@example.com',
            ]);
        }

        $this->assertSame(
            ! $precognitive && $authorized && $valid ? 1 : 0,
            $action->runs,
        );
    }

    public static function predictions(): array
    {
        return ['valid' => [true], 'invalid' => [false]];
    }

    #[DataProvider('predictions')]
    public function test_prediction_isolates_and_preserves_method_binding(bool $valid): void
    {
        ExampleController::$registry['contextual'] = ContextualAction::class;
        $action = new ContextualAction();
        $this->app->instance(ContextualAction::class, $action);
        $bindingRuns = 0;

        $this->app->bindMethod([ContextualAction::class, 'handle'], function () use (&$bindingRuns) {
            $bindingRuns++;

            return ['bound' => true];
        });

        $this->postJson('/actions?action=contextual')
            ->assertOk()->assertExactJson(['bound' => true]);
        $this->assertSame(1, $bindingRuns);

        $response = $this->postJson(
            '/actions?action=contextual',
            $valid ? ['name' => 'Fahad'] : [],
            ['Precognition' => 'true'],
        );

        if ($valid) {
            $response->assertNoContent()
                ->assertHeader('Precognition-Success', 'true');
        } else {
            $response->assertUnprocessable()->assertJsonValidationErrors(['name']);
        }

        $this->assertSame(1, $bindingRuns);
        $this->assertSame(0, $action->runs);

        $this->postJson('/actions?action=contextual')
            ->assertOk()->assertExactJson(['bound' => true]);
        $this->assertSame(2, $bindingRuns);
        $this->assertSame(0, $action->runs);
    }

    public static function requests(): array
    {
        return [
            'normal' => [false, true],
            'valid prediction' => [true, true],
            'invalid prediction' => [true, false],
        ];
    }

    #[DataProvider('requests')]
    public function test_context_matches_normal_dispatch(
        bool $precognitive,
        bool $valid,
    ): void {
        $resolved = [];

        $this->app->bind(ContextDependency::class, function () use (&$resolved) {
            $resolved[] = 'default';

            return new ContextDependency();
        });

        $this->app->when(ContextualAction::class)
            ->needs(ContextDependency::class)
            ->give(function () use (&$resolved) {
                $resolved[] = 'contextual';

                return new ContextDependency();
            });

        $request = Request::create(
            '/actions',
            'POST',
            $valid ? ['name' => 'Fahad'] : [],
        );

        // Exercise the dispatcher after middleware has prepared the request.
        $request->attributes->set('precognitive', $precognitive);
        $this->app->instance('request', $request);

        $action = new ContextualAction();
        $readBuildStack = Closure::bind(
            static fn (Container $container): array => $container->buildStack,
            null,
            Container::class,
        );
        $contextBefore = $readBuildStack($this->app);

        try {
            $result = ActionDispatcher::dispatch($this->app, $action, $request);

            $this->assertFalse($precognitive);
            $this->assertSame(['name' => 'Fahad'], $result);
        } catch (HttpException $exception) {
            $this->assertTrue($precognitive);
            $this->assertTrue($valid);
            $this->assertSame(204, $exception->getStatusCode());
            $this->assertSame(
                'true',
                $exception->getHeaders()['Precognition-Success'] ?? null,
            );
        } catch (ValidationException $exception) {
            $this->assertFalse($valid);
            $this->assertArrayHasKey('name', $exception->errors());
        }

        $this->assertSame(['contextual'], $resolved);
        $this->assertSame($contextBefore, $readBuildStack($this->app));
        $this->assertSame($precognitive ? 0 : 1, $action->runs);
    }
}

class ContextDependency
{
}

class ContextualAction extends Action
{
    protected array $methods = ['POST'];

    protected bool $precognition = true;

    public int $runs = 0;

    public function handle(
        ContextDependency $dependency,
        ContextualRequest $request,
    ): array {
        $this->runs++;

        return $request->validated();
    }
}

class ContextualRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['name' => ['required', 'string']];
    }
}

class SubstitutedContextualRequest extends ContextualRequest
{
    public function authorize(): bool
    {
        return $this->boolean('allowed');
    }

    public function rules(): array
    {
        return ['email' => ['required', 'email']];
    }
}
