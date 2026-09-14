<?php

namespace Exert\Console;

use Illuminate\Console\Command;

/**
 * Create the application's Exert configuration from the package defaults.
 */
class ConfigCommand extends Command
{
    protected $signature = 'exert:config {--force : Overwrite the existing Exert configuration file}';

    protected $description = 'Create the Exert configuration file';

    public function handle(): int
    {
        $target = $this->laravel->configPath('exert.php');

        if (file_exists($target) && ! $this->option('force')) {
            $this->components->error(
                'The Exert configuration already exists. Use --force to overwrite it.'
            );

            return self::FAILURE;
        }

        $files = $this->laravel['files'];

        $files->ensureDirectoryExists(dirname($target));
        $files->copy(__DIR__.'/../../config/exert.php', $target);

        $this->components->info('Exert configuration created at ['.$target.'].');

        return self::SUCCESS;
    }
}
