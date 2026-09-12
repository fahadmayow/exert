<?php

namespace Exert\Tests\Feature;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

require_once __DIR__.'/../../scripts/laravel-matrix.php';

class LaravelVersionMatrixTest extends TestCase
{
    public function test_it_generates_an_ordered_matrix_of_stable_versions(): void
    {
        $metadata = ['packages' => ['laravel/framework' => [
            ['version' => 'v13.1.0'],
            ['version' => '12.x-dev'],
            ['version' => 'v12.0.0-RC1'],
            ['version' => 'v12.0.1'],
            ['version' => 'v12.50.2'],
            ['version' => 'v12.51.0'],
            ['version' => 'v12.51.1'],
            ['version' => 'v10.48.0'],
            ['version' => 'v14.0.0'],
        ]]];

        $this->assertSame([
            'include' => [
                [
                    'laravel' => '12.51.x',
                    'versions' => ['12.51.0', '12.51.1'],
                    'php' => '8.2',
                    'testbench' => '^10.0',
                ],
                [
                    'laravel' => '13.1.x',
                    'versions' => ['13.1.0'],
                    'php' => '8.3',
                    'testbench' => '^11.0',
                ],
            ],
        ], \LaravelVersionMatrix::generate($metadata, '12.x-13.x'));
    }

    public function test_it_accepts_a_single_supported_major(): void
    {
        $metadata = ['packages' => ['laravel/framework' => [
            ['version' => 'v12.51.0'],
            ['version' => 'v13.0.0'],
        ]]];

        $this->assertSame(['include' => [[
            'laravel' => '12.51.x',
            'versions' => ['12.51.0'],
            'php' => '8.2',
            'testbench' => '^10.0',
        ]]], \LaravelVersionMatrix::generate($metadata, '12.x'));
    }

    public static function invalidRanges(): array
    {
        return [
            'wrong syntax' => ['11-13'],
            'descending' => ['13.x-12.x'],
            'unsupported start' => ['11.x-13.x'],
            'unsupported end' => ['12.x-14.x'],
        ];
    }

    #[DataProvider('invalidRanges')]
    public function test_it_rejects_invalid_or_unsupported_ranges(string $range): void
    {
        $this->expectException(InvalidArgumentException::class);
        \LaravelVersionMatrix::generate([], $range);
    }
}
