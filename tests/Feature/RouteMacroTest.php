<?php

namespace Exert\Tests\Feature;

use Exert\Tests\TestCase;
use Exert\Tests\Fixtures\{ExampleController, FirstMiddleware};
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
        $this->postJson('/api/limited', ['action' => 'example'])->assertOk()->assertHeader('X-First', 'yes');
        $this->assertSame('limited', $this->app['request']->route()->getName());
        $this->getJson('/api/multiple?action=example')->assertOk();
    }

    public function test_route_middleware_can_reject_invalid_actions_before_selection(): void
    {
        Route::exert('/protected', ExampleController::class)->middleware(\Exert\Tests\Fixtures\BlockMiddleware::class);
        $this->getJson('/protected?action=missing')->assertForbidden();
        $this->assertNull($this->app['request']->attributes->get('exert.action_id'));
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
}
