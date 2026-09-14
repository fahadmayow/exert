<?php

namespace Exert\Console;

use Illuminate\Console\Command;

/**
 * Create the application's messages configuration from the package defaults.
 */
class MessagesCommand extends Command
{
    protected $signature = 'exert:messages {--force : Overwrite the existing messages configuration file}';

    protected $description = 'Create the HTTP status messages configuration file';

    public function handle(): int
    {
        $target = $this->laravel->configPath('messages.php');

        if (file_exists($target) && ! $this->option('force')) {
            $this->components->error(
                'The messages configuration already exists. Use --force to overwrite it.'
            );

            return self::FAILURE;
        }

        $files = $this->laravel['files'];

        $files->ensureDirectoryExists(dirname($target));
        $files->copy(__DIR__.'/../../config/messages.php', $target);

        $this->components->info('Messages configuration created at ['.$target.'].');

        return self::SUCCESS;
    }
}
