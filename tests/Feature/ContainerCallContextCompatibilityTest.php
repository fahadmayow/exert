<?php

namespace Exert\Tests\Feature;

use Closure;
use Exert\ContainerCallContext;
use Illuminate\Container\Container;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use RuntimeException;

class ContainerCallContextCompatibilityTest extends TestCase
{
    public function test_required_container_internals_exist_and_keep_their_expected_behavior(): void
    {
        $reflection = new ReflectionClass(Container::class);

        $this->assertTrue($reflection->hasMethod('getClassForCallable'));
        $method = $reflection->getMethod('getClassForCallable');
        $this->assertTrue($method->isProtected());
        $this->assertSame(1, $method->getNumberOfRequiredParameters());

        $this->assertTrue($reflection->hasProperty('buildStack'));
        $this->assertTrue($reflection->getProperty('buildStack')->isProtected());

        $container = new Container;
        $callback = [new ContainerCompatibilityCallable, 'handle'];
        $inspect = Closure::bind(
            static fn (Container $container, callable $callback): array => [
                $container->getClassForCallable($callback),
                $container->buildStack,
            ],
            null,
            Container::class,
        );

        [$class, $stack] = $inspect($container, $callback);
        $this->assertSame(ContainerCompatibilityCallable::class, $class);
        $this->assertSame([], $stack);

        $result = ContainerCallContext::run(
            $container,
            $callback,
            function () use ($container, $callback, $inspect): string {
                [$class, $stack] = $inspect($container, $callback);
                $this->assertSame(ContainerCompatibilityCallable::class, $class);
                $this->assertSame([ContainerCompatibilityCallable::class], $stack);

                return 'resolved';
            },
        );

        $this->assertSame('resolved', $result);
        $this->assertSame([], $inspect($container, $callback)[1]);

        try {
            ContainerCallContext::run(
                $container,
                $callback,
                static fn () => throw new RuntimeException('resolution failed'),
            );

            $this->fail('Expected the resolver to throw.');
        } catch (RuntimeException $exception) {
            $this->assertSame('resolution failed', $exception->getMessage());
        }

        $this->assertSame([], $inspect($container, $callback)[1]);
    }
}

class ContainerCompatibilityCallable
{
    public function handle(): void
    {
    }
}
