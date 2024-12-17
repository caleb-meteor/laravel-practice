<?php

namespace Caleb\Practice\Commands;

use Illuminate\Console\GeneratorCommand;

class ServiceMakeCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'practice:make:service';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new service class';


    protected function getStub()
    {
        return $this->resolveStubPath('/stubs/service.stub');
    }

    protected function resolveStubPath($stub)
    {
        return file_exists($customPath = $this->laravel->basePath(trim($stub, '/')))
            ? $customPath
            : __DIR__.$stub;
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\Services';
    }


}
