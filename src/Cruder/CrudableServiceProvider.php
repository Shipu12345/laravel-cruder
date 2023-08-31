<?php

namespace Shipu\Cruder;

use Shipu\Cruder\Contracts\Crud;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\ServiceProvider;

class CrudableServiceProvider extends ServiceProvider
{

    public function boot(): void
    {
        //Publish config and translations
        $this->publishes([
            __DIR__ . '/../config/crudable.php' => config_path('crudable.php'),
        ]);
        //Add views
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'crudable');

        //Add Routes
        $this->loadRoutesFrom(__DIR__.'/../resources/routes.php');

        // Add Migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations',);
    }

    /**
     * Register the service provider.
     * @throws BindingResolutionException
     */
    public function register(): void
    {
        //register commands
        $this->commands([
            Commands\CrudCommand::class,
            Commands\ControllerCommand::class,
            Commands\ServiceCommand::class,
            Commands\ContractCommand::class,
            Commands\ViewCommand::class,
            Commands\MigrationCommand::class,
        ]);
        //Merge config
        $this->mergeConfigFrom(
            __DIR__ . '/../config/crudable.php',
            'crudable'
        );
        //Load config
        $config = $this->app->make('config');
        //Check for auto binding
        if ($config->get('crudable.use_auto_binding')) {
            //Run contextual binding first
            foreach ($config->get('crudable.implementations') as $usage) {
                $this->app->when($usage['when'])
                    ->needs($usage['needs'] ?? Crud::class)
                    ->give($usage['give']);
            }
            //Run fixed bindings
            foreach ($config->get('crudable.bindings') as $binding) {
                $this->app->bind($binding['contract'], $binding['target']);
            }
        }
    }
}
