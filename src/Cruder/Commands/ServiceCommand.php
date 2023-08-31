<?php

namespace Shipu\Cruder\Commands;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Str;
use Illuminate\Console\GeneratorCommand;

class ServiceCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crud:service {name} {--contract}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates a Crud Service class';

    protected $type = 'Service';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub(): string
    {
        if ($this->option('contract')) {
            return __DIR__ . '/../../resources/stubs/contract/service.stub';
        }
        return __DIR__ . '/../../resources/stubs/service.stub';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param string $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace . '\\' . config('crudable.default_namespace');
    }

    /**
     * Replace the service variable in the stub
     *
     * @param string $name
     * @return string
     */
    protected function replaceDummyModelNamespace(string $name): string
    {
        $namespace = $this->replaceDummyModel($name);
        if ($this->laravel->version() >= 8) {
            return $this->rootNamespace() . 'Models\\' . $namespace;
        }
        return $this->rootNamespace() . $namespace;
    }

    protected function replaceServiceVar($name): string
    {
        $class = str_replace($this->getNamespace($name) . '\\', '', $name);
        return strtolower(Str::snake(str_replace('Service', '', $class)));
    }

    protected function replaceDummyModel($name): array|string
    {
        $class = str_replace($this->getNamespace($name) . '\\', '', $name);
        return str_replace('Service', '', $class);
    }

    protected function replaceDummyContract($name): array|string
    {
        $class = str_replace($this->getNamespace($name) . '\\', '', $name);
        return str_replace('Service', 'Contract', $class);
    }

    /**
     * Build the class with the given name.
     *
     * Remove the base controller import if we are already in base namespace.
     *
     * @param string $name
     * @return string
     * @throws FileNotFoundException
     */
    protected function buildClass($name): string
    {
        $replace = [
            'DummyModelNamespace' => $this->replaceDummyModelNamespace($name),
            'DummyServiceVar' => Str::snake($this->replaceServiceVar($name)),
            'DummyModel' => $this->replaceDummyModel($name),
            'DummyContract' => $this->replaceDummyContract($name)
        ];
        return str_replace(
            array_keys($replace),
            array_values($replace),
            parent::buildClass($name)
        );
    }

    /**
     * Execute the console command.
     *
     * @throws FileNotFoundException
     */
    public function handle()
    {
        $this->comment('Building new Crudable service class.');

        $name = $this->qualifyClass($this->getNameInput());
        $path = $this->getPath($name);
        if ($this->alreadyExists($this->getNameInput())) {
            $this->error($this->type . ' already exists!');

            return false;
        }

        // Next, we will generate the path to the location where this class' file should get
        // written. Then, we will build the class and make the proper replacements on the
        // stub files so that it gets the correctly formatted namespace and class name.
        $this->makeDirectory($path);
        $this->files->put($path, $this->buildClass($name));
        $this->info($this->type . ' created successfully.');
    }
}
