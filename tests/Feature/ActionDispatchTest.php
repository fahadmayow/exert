<?php

namespace Exert\Tests\Feature;

use Exert\Tests\TestCase;
use Exert\Tests\Fixtures\{ExampleAction, ExampleController, HiddenHandlerAction};
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ActionDispatchTest extends TestCase
{
    public function test_default_key_and_dependency_injection(): void
    {
        $this->assertSame('action', config('exert.action_key'));
        $this->assertSame('query', config('exert.action_source'));
        $this->getJson('/actions?action=example')->assertOk()->assertExactJson(['ok' => true]);
        $this->assertSame(['injected:injected'], ExampleAction::$trace);
        $this->postJson('/actions?action=example', ['action' => 'missing'])->assertOk();
        $this->postJson('/actions', ['action' => 'example'])->assertNotFound();
    }

    public function test_body_source_reads_json_and_form_data_only(): void
    {
        config(['exert.action_source' => 'body', 'exert.action_key' => 'operation']);
        $this->postJson('/actions?operation=missing', ['operation' => 'example'])->assertOk();
        $this->post('/actions?operation=missing', ['operation' => 'example'])->assertOk();
        $this->postJson('/actions?operation=example', [])->assertNotFound();
        $this->post('/actions?operation=example', [])->assertNotFound();
        $this->get('/actions?operation=example')->assertNotFound();
    }

    public function test_both_source_preserves_input_behavior(): void
    {
        config(['exert.action_source' => 'both']);
        $this->getJson('/actions?action=example')->assertOk();
        $this->postJson('/actions?action=example', [])->assertOk();
        $this->postJson('/actions?action=missing', ['action' => 'example'])->assertOk();
        $this->post('/actions?action=missing', ['action' => 'example'])->assertOk();
        $this->postJson('/actions?action=example', ['action' => 'missing'])->assertNotFound();
    }

    public function test_invalid_source_throws_configuration_exception(): void
    {
        config(['exert.action_source' => 'invalid']);
        $this->withoutExceptionHandling();
        $this->expectException(\LogicException::class);
        $this->getJson('/actions?action=example');
    }

    public function test_custom_key_and_legacy_key_rejection(): void
    {
        $this->getJson('/actions?__action=example')->assertNotFound();
        config(['exert.action_key' => 'operation']);
        $this->getJson('/actions?operation=example')->assertOk();
        $this->getJson('/actions?action=example')->assertNotFound();
    }

    public static function invalidInputs(): array
    {
        return [[[]], [['action' => 'missing']], [['action' => []]], [['action' => 12]], [['action' => null]]];
    }

    #[DataProvider('invalidInputs')]
    public function test_invalid_selection_returns_404(array $input): void
    {
        config(['exert.action_source' => 'body']);
        $this->postJson('/actions', $input)->assertNotFound();
        $this->assertSame(0, ExampleAction::$runs);
    }

    public function test_missing_class_returns_404(): void
    {
        ExampleController::$registry['missing'] = 'Exert\Tests\MissingClass';
        $this->getJson('/actions?action=missing')->assertNotFound();
    }

    public function test_invalid_contract_throws_configuration_exception(): void
    {
        ExampleController::$registry['invalid'] = \stdClass::class;
        $this->withoutExceptionHandling();
        $this->expectException(\LogicException::class);
        $this->getJson('/actions?action=invalid');
    }

    public function test_handler_must_be_public(): void
    {
        ExampleController::$registry['hidden'] = HiddenHandlerAction::class;
        $this->withoutExceptionHandling();
        $this->expectException(\LogicException::class);
        $this->getJson('/actions?action=hidden');
    }

    public function test_405_lists_normalized_unique_methods(): void
    {
        $this->putJson('/actions?action=example', [])->assertStatus(405)->assertHeader('Allow', 'GET, POST');
        $this->assertSame(0, ExampleAction::$runs);
        $this->assertSame('example', $this->app['request']->attributes->get('exert.action'));
    }

    public function test_default_get_and_explicit_head_requirement(): void
    {
        $this->get('/actions?action=default')->assertOk()->assertContent('default');
        $this->call('HEAD', '/actions', ['action' => 'default'])->assertStatus(405)->assertHeader('Allow', 'GET');
    }

    public function test_existing_responses_are_preserved(): void
    {
        ExampleAction::$result = redirect('/next')->withHeaders(['X-Custom' => 'yes']);
        $this->get('/actions?action=example')->assertRedirect('/next')->assertHeader('X-Custom', 'yes');
    }

    public function test_metadata_uses_registry_values_and_clears_on_failed_reselection(): void
    {
        $this->getJson('/actions?action=example&exert.action_id=spoofed')->assertOk();
        $this->assertSame(ExampleController::class.'::example', ExampleAction::$metadata['exert.action_id']);
        $this->assertSame(ExampleAction::class, ExampleAction::$metadata['exert.action_class']);
        $request = Request::create('/', 'GET', ['action' => 'missing']);
        $request->attributes->add(ExampleAction::$metadata);
        try {
            (new ExampleController)->resolve($request);
            $this->fail('Expected a not-found exception');
        } catch (NotFoundHttpException) {
            foreach (['exert.action', 'exert.action_class', 'exert.controller', 'exert.action_id'] as $key) {
                $this->assertFalse($request->attributes->has($key));
            }
        }
    }
}
