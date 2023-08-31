<?php

namespace Shipu\Cruder\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\GeneratorCommand;

class ViewCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crud:views {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates Crud views';

    protected $type = 'Views';
    private $current_stub;

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub(): array|string
    {
        return [
            'index.blade.php' => __DIR__ . '/../../resources/stubs/' . config('crudable.css_framework') . '/index.stub',
            'create.blade.php' => __DIR__ . '/../../resources/stubs/' . config('crudable.css_framework') . '/create.stub',
            'edit.blade.php' => __DIR__ . '/../../resources/stubs/' . config('crudable.css_framework') . '/edit.stub'
        ];
    }

    protected function getPath($name): string
    {
        return resource_path('views/admin/' . $this->getDirectoryName($name));
    }

    protected function getDirectoryName($name): string
    {
        return  Str::plural(strtolower(Str::kebab($name)));
    }

    protected function replaceServiceVar($name): string
    {
        $class = str_replace($this->getNamespace($name) . '\\', '', $name);
        $class = strtolower(Str::snake(str_replace('Service', '', $class)));
        return Str::plural($class);
    }


    protected function replaceSingularServiceVar($name): string
    {
        $class = str_replace($this->getNamespace($name) . '\\', '', $name);
        return strtolower(Str::snake(str_replace('Service', '', $class)));
    }

    protected function replaceViewPath($name): string
    {
        return Str::plural(Str::kebab(str_replace($this->getNamespace($name) . '\\', '', $name)));
    }

    /**
     * Build the class with the given name.
     *
     * Remove the base controller import if we are already in base namespace.
     *
     * @param  string  $name
     * @return string
     */
    protected function buildClass($name): string
    {
        $controllerNamespace = $this->getNamespace($name);
        $replace = [
            'DummyServiceVar' => $this->replaceServiceVar($name),
            'DummyViewPath' => $this->replaceViewPath($name),
            'DummySingularServiceVar' => $this->replaceSingularServiceVar($name)
        ];
        return str_replace(
            array_keys($replace),
            array_values($replace),
            $this->generateClass($name)
        );
    }

    protected function generateClass($name): string
    {
        $stub = $this->files->get($this->current_stub);
        return $this->replaceNamespace($stub, $name)->replaceClass($stub, $name);
    }

    protected function alreadyExists($rawName): bool
    {
        return $this->files->exists($this->getPath($this->getNameInput()));
    }

    public function handle()
    {
        $this->comment('Building new Crudable views.');

        $path = $this->getPath(strtolower(Str::kebab($this->getNameInput())));
        if ($this->alreadyExists($this->getNameInput())) {
            $this->error($this->type . ' already exist!');
            return false;
        }

        // Next, we will generate the path to the location where this class' file should get
        // written. Then, we will build the class and make the proper replacements on the
        // stub files so that it gets the correctly formatted namespace and class name.
        foreach ($this->getStub() as $name => $stub) {
            $this->current_stub = $stub;
            $this->makeDirectory($path . '/' . $name);
            $this->files->put($path . '/' . $name, $this->buildClass($this->getNameInput()));
        }
        $this->info($this->type . ' created successfully.');
    }
}
