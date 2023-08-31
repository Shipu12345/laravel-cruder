<?php

namespace Shipu\Cruder\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;

class MigrationCommand extends GeneratorCommand
{
    protected $signature = 'crud:migration {name}';
    protected $description = 'Generates a Crud migration class';
    protected $type = 'Migration';

    protected function getStub(): string
    {
        return __DIR__ . '/../../resources/stubs/database/migrations/migration_file.stub';
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace . '\\..\\' . "database" . '\\' . "migrations";
    }


    protected function replaceMigrationVar($name): string
    {
        $class = str_replace($this->getNamespace($name) . '\\', '', $name);
        return strtolower(Str::snake(str_replace('Service', '', $class)));
    }

    private function addTimeStampWithCurrentPath(string $oldPath, string $name): string
    {
        $newFileName = strtolower($name);
        $newFileName = now()->format('Y_m_d_His') . "_create_{$newFileName}_table.php";
        return dirname($oldPath) . '/' . $newFileName;
    }

    protected function buildClass($name): string
    {
        $replace = [
            'migration_table_name_table' => Str::lower($this->replaceMigrationVar($name)),
        ];
        return str_replace(
            array_keys($replace),
            array_values($replace),
            parent::buildClass($name)
        );
    }

    public function handle()
    {
        $this->comment('Building new Migration file.');

        $name = $this->qualifyClass($this->getNameInput());
        $path = $this->addTimeStampWithCurrentPath($this->getPath($name), $this->getNameInput());

        if ($this->alreadyExists($this->getNameInput())) {
            $this->error($this->type . ' already exists!');

            return false;
        }

        $this->makeDirectory($path);
        $this->files->put($path, $this->buildClass($name));
        $this->info($this->type . ' created successfully.');
    }
}
