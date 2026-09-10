<?php

namespace Exert\Console;

use Illuminate\Console\Concerns\CreatesMatchingTest;
use Illuminate\Console\GeneratorCommand;
use InvalidArgumentException;
use Symfony\Component\Console\Input\InputOption;

/**
 * Generate actions in the configured application directory.
 */
class ActionMakeCommand extends GeneratorCommand
{
    use CreatesMatchingTest;

    protected $name = 'make:action';

    protected $description = 'Create a new action class';

    protected $type = 'Action';

    /**
     * Allow applications to override the package's action template.
     */
    protected function getStub()
    {
        $customPath = $this->laravel->basePath('stubs/action.stub');

        return $this->files->exists($customPath)
            ? $customPath
            : __DIR__.'/stubs/action.stub';
    }

    /**
     * Match the namespace to the configured directory under the app path.
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        $path = config('exert.actions_path', 'Actions');

        if (!is_string($path) || $path === '') {
            throw new InvalidArgumentException('exert.actions_path must be a non-empty directory relative to app/.');
        }

        $segments = explode('/', str_replace('\\', '/', $path));

        foreach ($segments as $segment) {
            if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/D', $segment)) {
                throw new InvalidArgumentException('exert.actions_path must contain valid PHP namespace segments relative to app/.');
            }
        }

        return $rootNamespace.'\\'.implode('\\', $segments);
    }

    protected function getOptions()
    {
        return [
            ['force', null, InputOption::VALUE_NONE, 'Create the class even if the action already exists'],
        ];
    }
}
