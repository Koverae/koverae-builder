<?php

namespace Koverae\KoveraeBuilder;

use Illuminate\Support\ServiceProvider;
use Koverae\KoveraeBuilder\Commands\MakeCartCommand;
use Koverae\KoveraeBuilder\Commands\MakeControlPanelCommand;
use Koverae\KoveraeBuilder\Commands\MakeFormCommand;
use Koverae\KoveraeBuilder\Commands\MakePageCommand;
use Koverae\KoveraeBuilder\Commands\MakeTableCommand;
use Koverae\KoveraeBuilder\Commands\ModuleMakeCommand;
use Koverae\KoveraeBuilder\Commands\PackageInstallMessageCommand;

class KoveraeBuilderServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        /*
         * Optional methods to load your package assets
         */
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'koverae-builder');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'koverae-builder');
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        // $this->loadRoutesFrom(__DIR__.'/routes.php');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('koverae-builder.php'),
            ], 'config');

            // Publishing the views.
            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/koverae-builder'),
            ], 'views');

            // Publishing assets.
            /*$this->publishes([
                __DIR__.'/../resources/assets' => public_path('vendor/koverae-builder'),
            ], 'assets');*/

            // Publishing the translation files.
            /*$this->publishes([
                __DIR__.'/../resources/lang' => resource_path('lang/vendor/koverae-builder'),
            ], 'lang');*/

            // Registering package commands.
            $this->commands([
                MakeControlPanelCommand::class,
                MakeFormCommand::class,
                ModuleMakeCommand::class,
                MakeTableCommand::class,
                MakeCartCommand::class,
                MakePageCommand::class,
                PackageInstallMessageCommand::class,
            ]);
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'koverae-builder');

        // Register the main class to use with the facade
        $this->app->singleton('koverae-builder', function () {
            return new KoveraeBuilder;
        });
    }
}
