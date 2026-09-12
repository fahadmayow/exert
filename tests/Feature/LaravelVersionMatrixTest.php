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
            ['version' => 'v11.2.0'],
            ['version' => 'v12.0.0-RC1'],
            ['version' => 'v12.0.1'],
            ['version' => 'v10.48.0'],
            ['version' => 'v14.0.0'],
            ['version' => 'v11.1.0'],
            ['version' => 'v11.1.1'],
        ]]];

        $this->assertSame([
            'include' => [
                [
                    'laravel' => '11.1.x',
                    'versions' => ['11.1.0', '11.1.1'],
                    'php' => '8.2',
                    'testbench' => '^9.0',
                ],
                [
                    'laravel' => '11.2.x',
                    'versions' => ['11.2.0'],
                    'php' => '8.2',
                    'testbench' => '^9.0',
                ],
                [
                    'laravel' => '12.0.x',
                    'versions' => ['12.0.1'],
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
        ], \LaravelVersionMatrix::generate($metadata, '11.x-13.x'));
    }

    public static function invalidRanges(): array
    {
        return [
            'wrong syntax' => ['11-13'],
            'descending' => ['13.x-11.x'],
            'unsupported start' => ['10.x-13.x'],
            'unsupported end' => ['11.x-14.x'],
        ];
    }

    #[DataProvider('invalidRanges')]
    public function test_it_rejects_invalid_or_unsupported_ranges(string $range): void
    {
        $this->expectException(InvalidArgumentException::class);
        \LaravelVersionMatrix::generate([], $range);
    }
}
