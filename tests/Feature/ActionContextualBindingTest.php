<?php

namespace Exert\Tests\Feature;

use Exert\Action;
use Exert\ActionDispatcher;
use Exert\Tests\TestCase;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ActionContextualBindingTest extends TestCase
{
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
        $contextBefore = $this->app->currentlyResolving();

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
        $this->assertSame($contextBefore, $this->app->currentlyResolving());
        $this->assertSame($precognitive ? 0 : 1, $action->runs);
    }
}

class ContextDependency
{
}

class ContextualAction extends Action
{
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
