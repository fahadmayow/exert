<?php

final class LaravelVersionMatrix
{
    private const SUPPORTED_MAJORS = [
        12 => ['minimum' => '12.51.0', 'php' => '8.2', 'testbench' => '^10.0'],
        13 => ['minimum' => '13.0.0', 'php' => '8.3', 'testbench' => '^11.0'],
    ];

    public static function generate(array $metadata, string $range): array
    {
        [$firstMajor, $lastMajor] = self::parseRange($range);
        $minorLines = [];

        foreach ($metadata['packages']['laravel/framework'] ?? [] as $package) {
            $version = ltrim((string) ($package['version'] ?? ''), 'v');

            if (preg_match('/\A(\d+)\.(\d+)\.(\d+)\z/', $version, $matches) !== 1) {
                continue;
            }

            $major = (int) $matches[1];

            if (
                $major < $firstMajor ||
                $major > $lastMajor ||
                version_compare($version, self::SUPPORTED_MAJORS[$major]['minimum'], '<')
            ) {
                continue;
            }

            $minorLines[$major.'.'.$matches[2]][$version] = $major;
        }

        uksort($minorLines, 'version_compare');

        if ($minorLines === []) {
            throw new RuntimeException('No stable Laravel releases matched '.$range.'.');
        }

        if (count($minorLines) > 256) {
            throw new RuntimeException(
                'The range matched more than GitHub Actions\' 256-job matrix limit after grouping patches by minor line.'
            );
        }

        return [
            'include' => array_map(
                static function (string $minor, array $versions): array {
                    uksort($versions, 'version_compare');
                    $major = reset($versions);

                    return [
                        'laravel' => $minor.'.x',
                        'versions' => array_keys($versions),
                        'php' => self::SUPPORTED_MAJORS[$major]['php'],
                        'testbench' => self::SUPPORTED_MAJORS[$major]['testbench'],
                    ];
                },
                array_keys($minorLines),
                array_values($minorLines),
            ),
        ];
    }

    private static function parseRange(string $range): array
    {
        if (preg_match('/\A(\d+)\.x(?:-(\d+)\.x)?\z/', $range, $matches) !== 1) {
            throw new InvalidArgumentException(
                'Laravel range must use the format 12.x or 12.x-13.x.'
            );
        }

        $firstMajor = (int) $matches[1];
        $lastMajor = isset($matches[2]) && $matches[2] !== ''
            ? (int) $matches[2]
            : $firstMajor;
        $supportedMajors = array_keys(self::SUPPORTED_MAJORS);

        if (
            $firstMajor > $lastMajor ||
            !in_array($firstMajor, $supportedMajors, true) ||
            !in_array($lastMajor, $supportedMajors, true)
        ) {
            throw new InvalidArgumentException(
                'Laravel range must stay between 12.x and 13.x in ascending order.'
            );
        }

        return [$firstMajor, $lastMajor];
    }
}

if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    try {
        $range = $argv[1] ?? '12.x-13.x';
        $source = getenv('PACKAGIST_METADATA_FILE');
        $json = $source
            ? file_get_contents($source)
            : file_get_contents('https://repo.packagist.org/p2/laravel/framework.json');

        if ($json === false) {
            throw new RuntimeException('Unable to load Laravel release metadata from Packagist.');
        }

        $metadata = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        echo json_encode(
            LaravelVersionMatrix::generate($metadata, $range),
            JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR,
        );
    } catch (Throwable $exception) {
        fwrite(STDERR, $exception->getMessage().PHP_EOL);
        exit(1);
    }
}
