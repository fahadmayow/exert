<?php

namespace Exert\Tests\Feature;

use Exert\Tests\TestCase;
use Exert\Tests\Fixtures\{ExampleAction, FirstMiddleware, LastMiddleware, ParameterMiddleware};
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ActionMiddlewareTest extends TestCase
{
    public function test_route_exclusions_apply_to_action_middleware(): void
    {
        $router = $this->app['router'];
        $router->aliasMiddleware('first', FirstMiddleware::class);
        $router->middlewareGroup('checks', ['first']);
        $router->getRoutes()->getByName('actions')->withoutMiddleware('checks');
        ExampleAction::$middleware = [FirstMiddleware::class, LastMiddleware::class];

        $this->getJson('/actions?action=example')->assertOk()->assertHeaderMissing('X-First');
        $this->assertSame(['last', 'injected:injected'], ExampleAction::$trace);

        // Exclusions belong to the route, not the action class.
        ExampleAction::$trace = [];
        $this->getJson('/other?action=example')->assertOk()->assertHeader('X-First', 'yes');
        $this->assertSame(['first', 'last', 'injected:injected'], ExampleAction::$trace);
    }

    public function test_without_middleware_skips_action_middleware(): void
    {
        ExampleAction::$middleware = [FirstMiddleware::class, fn ($request, $next) => response('blocked', 403)];
        $this->withoutMiddleware();

        $this->getJson('/actions?action=example')->assertOk()->assertHeaderMissing('X-First');
        $this->assertSame(['injected:injected'], ExampleAction::$trace);
        $this->assertSame(1, ExampleAction::$runs);
    }

    public function test_false_disable_flag_keeps_action_middleware_enabled(): void
    {
        $this->app->instance('middleware.disable', false);
        ExampleAction::$middleware = [FirstMiddleware::class];

        $this->getJson('/actions?action=example')->assertOk()->assertHeader('X-First', 'yes');
        $this->assertSame(['first', 'injected:injected'], ExampleAction::$trace);
    }

    public function test_aliases_nested_groups_parameters_priority_and_deduplication(): void
    {
        $router = $this->app['router'];
        $router->aliasMiddleware('first', FirstMiddleware::class);
        $router->aliasMiddleware('last', LastMiddleware::class);
        $router->aliasMiddleware('parameters', ParameterMiddleware::class);
        $router->middlewareGroup('nested', ['first']);
        $router->middlewareGroup('checks', ['parameters:one,two', 'nested']);
        $this->app->make(\Illuminate\Contracts\Http\Kernel::class)->setMiddlewarePriority([FirstMiddleware::class, ParameterMiddleware::class, LastMiddleware::class]);
        ExampleAction::$middleware = ['last', 'checks', 'first'];
        $this->getJson('/actions?action=example')->assertOk()->assertHeader('X-First', 'yes');
        $this->assertSame(['first', 'one:two', 'last', 'injected:injected'], ExampleAction::$trace);
    }

    public function test_early_response_skips_handler(): void
    {
        ExampleAction::$middleware = [FirstMiddleware::class, fn ($request, $next) => response('blocked', 403)];
        $this->getJson('/actions?action=example')->assertForbidden()->assertHeader('X-First', 'yes');
        $this->assertSame(0, ExampleAction::$runs);
    }

    public function test_action_exception_is_rendered_before_outer_middleware_returns(): void
    {
        ExampleAction::$middleware = [FirstMiddleware::class];
        ExampleAction::$result = new HttpException(409, 'Conflict');
        $this->getJson('/actions?action=example')->assertStatus(409)->assertHeader('X-First', 'yes');
    }

    public function test_middleware_exception_is_rendered_before_outer_middleware_returns(): void
    {
        ExampleAction::$middleware = [FirstMiddleware::class, function () { throw new HttpException(403); }];
        $this->getJson('/actions?action=example')->assertForbidden()->assertHeader('X-First', 'yes');
        $this->assertSame(0, ExampleAction::$runs);
    }

    public function test_per_action_rate_limits_use_controller_qualified_identity(): void
    {
        RateLimiter::for('operations', fn ($request) => Limit::perMinute(1)->by($request->attributes->get('exert.action_id')));
        ExampleAction::$middleware = ['throttle:operations'];
        $this->getJson('/actions?action=example')->assertOk();
        $this->getJson('/actions?action=example')->assertStatus(429);
        $this->getJson('/other?action=example')->assertOk();
    }
}
