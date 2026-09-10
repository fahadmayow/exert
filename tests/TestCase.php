<?php

namespace Exert\Tests;

use Exert\ExertServiceProvider;
use Exert\Tests\Fixtures\{DefaultAction, ExampleAction, ExampleController, OtherController};

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app): array
    {
        return [ExertServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('cache.default', 'array');
        $app['config']->set('app.debug', false);
    }

    protected function defineRoutes($router): void
    {
        $router->exert('/actions', ExampleController::class)->name('actions');
        $router->exert('/other', OtherController::class);
    }

    protected function setUp(): void
    {
        parent::setUp();
        ExampleController::$registry = ['example' => ExampleAction::class, 'default' => DefaultAction::class];
        ExampleAction::$middleware = ExampleAction::$trace = ExampleAction::$metadata = [];
        ExampleAction::$runs = 0;
        ExampleAction::$result = ['ok' => true];
    }
}
