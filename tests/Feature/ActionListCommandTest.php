<?php

namespace Exert\Tests\Feature;

use Exert\Tests\TestCase;
use Exert\Tests\Fixtures\{ExampleAction, ExampleController};
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

class ActionListCommandTest extends TestCase
{
    public function test_json_lists_metadata_without_running_actions(): void
    {
        ExampleAction::$middleware = ['auth'];
        Route::exert('/restricted', ExampleController::class, 'PATCH');
        $this->assertSame(0, Artisan::call('exert:list', ['--json' => true]));
        $rows = collect(json_decode(Artisan::output(), true, 512, JSON_THROW_ON_ERROR));
        $row = $rows->first(fn ($row) => $row['uri'] === '/actions' && $row['action'] === 'example');
        $this->assertSame(['GET', 'POST'], $row['effective_methods']);
        $this->assertSame(['auth'], $row['action_middleware']);
        $this->assertSame(ExampleController::class.'::example', $row['action_id']);
        $blocked = $rows->first(fn ($row) => $row['uri'] === '/restricted' && $row['action'] === 'example');
        $this->assertSame([], $blocked['effective_methods']);
        $this->assertSame(0, ExampleAction::$runs);
        $this->artisan('exert:list')->expectsOutputToContain('None (route blocked)')->assertSuccessful();
    }

    public function test_cached_routes_are_listed(): void
    {
        $routes = new \Illuminate\Routing\RouteCollection;
        $route = Route::exert('/cached-list', ExampleController::class)->name('cached-list');
        $route->prepareForSerialization();
        $routes->add($route);
        $this->app['router']->setCompiledRoutes($routes->compile());
        Artisan::call('exert:list', ['--json' => true]);
        $rows = json_decode(Artisan::output(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertCount(2, $rows);
        $this->assertSame('cached-list', $rows[0]['route_name']);
    }

    public function test_direct_action_routes_are_listed(): void
    {
        ExampleAction::$middleware = ['auth'];
        Route::exert('/direct-list')->action(ExampleAction::class, 'PATCH');

        Artisan::call('exert:list', ['--json' => true]);
        $rows = collect(json_decode(Artisan::output(), true, 512, JSON_THROW_ON_ERROR));
        $row = $rows->first(fn ($row) => $row['uri'] === '/direct-list');

        $this->assertNull($row['controller']);
        $this->assertNull($row['action']);
        $this->assertSame(ExampleAction::class, $row['action_id']);
        $this->assertSame(ExampleAction::class, $row['class']);
        $this->assertSame(['GET', 'POST'], $row['action_methods']);
        $this->assertSame([], $row['effective_methods']);
        $this->assertSame(['auth'], $row['action_middleware']);
        $this->assertSame(0, ExampleAction::$runs);

        $this->artisan('exert:list')
            ->expectsOutputToContain('(direct)')
            ->assertSuccessful();
    }

    public function test_cached_direct_action_route_is_listed(): void
    {
        $routes = new \Illuminate\Routing\RouteCollection;
        $route = Route::exert('/cached-direct-list')
            ->action(ExampleAction::class)
            ->name('cached-direct-list');
        $route->prepareForSerialization();
        $routes->add($route);
        $this->app['router']->setCompiledRoutes($routes->compile());

        Artisan::call('exert:list', ['--json' => true]);
        $rows = json_decode(Artisan::output(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertCount(1, $rows);
        $this->assertSame('cached-direct-list', $rows[0]['route_name']);
        $this->assertSame(ExampleAction::class, $rows[0]['action_id']);
    }
}
