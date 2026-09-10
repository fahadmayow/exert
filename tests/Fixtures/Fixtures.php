<?php

namespace Exert\Tests\Fixtures;

use Exert\Action;
use Exert\ActionController;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Service
{
    public function value(): string { return 'injected'; }
}

class ExampleAction extends Action
{
    protected array $methods = ['get', 'POST', 'GET'];
    public static array $middleware = [];
    public static array $trace = [];
    public static array $metadata = [];
    public static int $runs = 0;
    public static mixed $result = ['ok' => true];

    public function __construct(private Service $service) {}

    protected function middlewares(): array { return self::$middleware; }

    public function handle(Request $request, Service $service): mixed
    {
        self::$runs++;
        self::$trace[] = $service->value().':'.$this->service->value();
        self::$metadata = $request->attributes->all();
        if (self::$result instanceof \Throwable) { throw self::$result; }
        return self::$result;
    }
}

class DefaultAction extends Action
{
    public function handle(): string { return 'default'; }
}

class HiddenHandlerAction extends Action
{
    protected function handle(): string { return 'hidden'; }
}

class ExampleController extends ActionController
{
    public static array $registry = ['example' => ExampleAction::class, 'default' => DefaultAction::class];
    public function getActions(): array { return self::$registry; }
}

class OtherController extends ExampleController {}

class FirstMiddleware
{
    public function handle($request, $next): Response
    {
        ExampleAction::$trace[] = 'first';
        $response = $next($request);
        $response->headers->set('X-First', 'yes');
        return $response;
    }
}

class ParameterMiddleware
{
    public function handle($request, $next, $one, $two): Response
    {
        ExampleAction::$trace[] = $one.':'.$two;
        return $next($request);
    }
}

class LastMiddleware
{
    public function handle($request, $next): Response
    {
        ExampleAction::$trace[] = 'last';
        return $next($request);
    }
}

class BlockMiddleware
{
    public function handle($request, $next): Response
    {
        return new Response('blocked', 403);
    }
}
