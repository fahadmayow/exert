<?php

namespace Exert\Console;

use Illuminate\Console\Concerns\CreatesMatchingTest;
use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;

/**
 * Generate an action controller using Laravel's standard class generator.
 */
class ActionControllerMakeCommand extends GeneratorCommand
{
    use CreatesMatchingTest;

    protected $name = 'make:action-controller';

    protected $description = 'Create a new action controller class';

    protected $type = 'Controller';

    /**
     * Allow applications to override the package's controller template.
     */
    protected function getStub()
    {
        $customPath = $this->laravel->basePath('stubs/action-controller.stub');

        return $this->files->exists($customPath)
            ? $customPath
            : __DIR__.'/stubs/action-controller.stub';
    }

    /**
     * Place generated classes alongside the application's other controllers.
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\Http\Controllers';
    }

    protected function getOptions()
    {
        return [
            ['force', null, InputOption::VALUE_NONE, 'Create the class even if the controller already exists'],
        ];
    }
}
