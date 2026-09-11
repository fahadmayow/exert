<?php

namespace Exert\Tests\Feature;

use Exert\Tests\Fixtures\ExampleAction;
use Exert\Tests\Fixtures\ExampleController;
use Exert\Tests\TestCase;
use LogicException;
use PHPUnit\Framework\Attributes\DataProvider;

class ActionKeyConfigurationTest extends TestCase
{
    public static function invalidKeys(): iterable
    {
        $keys = [
            'dotted' => 'request.action',
            'brackets' => 'request[action]',
            'space' => 'request action',
            'hyphen' => 'request-action',
            'leading digit' => '1action',
            'trailing newline' => "action\n",
            'non-ASCII' => 'acción',
            'empty' => '',
            'null' => null,
            'array' => ['action'],
            'integer' => 123,
            'boolean' => false,
        ];

        foreach (['query', 'body', 'both'] as $source) {
            foreach (['json', 'form'] as $format) {
                foreach ($keys as $label => $key) {
                    yield "$source $format $label" => [$source, $format, $key];
                }
            }
        }
    }

    #[DataProvider('invalidKeys')]
    public function test_invalid_key_is_rejected_before_input_lookup(
        string $source,
        string $format,
        mixed $key,
    ): void {
        config(['exert.action_source' => $source, 'exert.action_key' => $key]);
        $this->withoutExceptionHandling();
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(
            'exert.action_key must start with a letter or underscore '
            .'and contain only letters, numbers, and underscores.'
        );

        if ($format === 'json') {
            $this->postJson('/actions?action=example', ['action' => 'example']);
        } else {
            $this->post('/actions?action=example', ['action' => 'example']);
        }
    }

    public static function validKeys(): iterable
    {
        foreach (['query', 'body', 'both'] as $source) {
            foreach (['action', '_action', 'Action_key2'] as $key) {
                yield "$source $key" => [$source, $key];
            }
        }
    }

    #[DataProvider('validKeys')]
    public function test_simple_keys_allow_dotted_action_identifiers(string $source, string $key): void
    {
        config(['exert.action_source' => $source, 'exert.action_key' => $key]);
        ExampleController::$registry['users.create'] = ExampleAction::class;

        $url = '/actions';
        $input = [$key => 'users.create'];

        if ($source === 'query') {
            $url .= '?'.http_build_query($input);
            $input = [];
        }

        $this->postJson($url, $input)->assertOk();
        $this->post($url, $input)->assertOk();
        $this->assertSame(2, ExampleAction::$runs);
    }
}
