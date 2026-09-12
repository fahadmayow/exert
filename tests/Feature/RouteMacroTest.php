<?php

namespace Exert\Tests\Feature;

use Exert\Action;
use Exert\Tests\TestCase;
use Exert\Tests\Fixtures\{ExampleController, FirstMiddleware};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class RouteMacroTest extends TestCase
{
    public function test_method_restrictions_and_chaining(): void
    {
        Route::prefix('api')->group(function () {
            Route::exert('/limited', ExampleController::class, allowedMethod: 'post')->middleware(FirstMiddleware::class)->name('limited');
            Route::exert('/multiple', ExampleController::class, ['GET', 'POST']);
        });
        $this->getJson('/api/limited?action=example')->assertStatus(405)->assertHeader('Allow', 'POST');
        $this->postJson('/api/limited?action=example', [])->assertOk()->assertHeader('X-First', 'yes');
        $this->assertSame('limited', $this->app['request']->route()->getName());
        $this->getJson('/api/multiple?action=example')->assertOk();
    }

    public function test_route_middleware_can_reject_invalid_actions_before_selection(): void
    {
        Route::exert('/protected', ExampleController::class)->middleware(\Exert\Tests\Fixtures\BlockMiddleware::class);
        $this->getJson('/protected?action=missing')->assertForbidden();
        $this->assertNull($this->app['request']->attributes->get('exert.action_id'));
    }

    public function test_fluent_controller_registration_supports_normal_route_chaining(): void
    {
        Route::exert('/fluent-controller')
            ->controller(ExampleController::class, 'POST')
            ->middleware(FirstMiddleware::class)
            ->name('fluent-controller');

        $this->getJson('/fluent-controller?action=example')->assertStatus(405);
        $this->postJson('/fluent-controller?action=example')->assertOk()
            ->assertHeader('X-First', 'yes');
        $this->assertSame('fluent-controller', $this->app['request']->route()->getName());
    }

    public function test_direct_action_registration_runs_the_action_lifecycle(): void
    {
        Route::exert('/direct/{value}')
            ->action(DirectRouteAction::class, ['GET', 'POST'])
            ->name('direct-action');

        $this->getJson('/direct/42')->assertOk()
            ->assertExactJson(['value' => '42'])
            ->assertHeader('X-First', 'yes');
        $this->assertSame('direct-action', $this->app['request']->route()->getName());

        $this->postJson('/direct/42')->assertStatus(405)->assertHeader('Allow', 'GET');
    }

    public function test_compiled_cached_routes_still_dispatch(): void
    {
        $router = $this->app['router'];
        $routes = new \Illuminate\Routing\RouteCollection;
        $route = Route::exert('/cached', ExampleController::class)->name('cached');
        $route->prepareForSerialization();
        $routes->add($route);
        $router->setCompiledRoutes($routes->compile());
        $this->getJson('/cached?action=example')->assertOk()->assertExactJson(['ok' => true]);
        $this->assertSame('cached', $this->app['request']->route()->getName());
    }

    public function test_compiled_cached_direct_action_route_still_dispatches(): void
    {
        $router = $this->app['router'];
        $routes = new \Illuminate\Routing\RouteCollection;
        $route = Route::exert('/cached-direct/{value}')
            ->action(DirectRouteAction::class)
            ->name('cached-direct');
        $route->prepareForSerialization();
        $routes->add($route);
        $router->setCompiledRoutes($routes->compile());

        $this->getJson('/cached-direct/42')->assertOk()
            ->assertExactJson(['value' => '42']);
        $this->assertSame('cached-direct', $this->app['request']->route()->getName());
    }
}

class DirectRouteAction extends Action
{
    protected function middleware(): array
    {
        return [FirstMiddleware::class];
    }

    public function handle(string $value, Request $request): array
    {
        return ['value' => $value];
    }
}
