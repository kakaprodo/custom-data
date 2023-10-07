<?php

namespace Kakaprodo\CustomData;

use Illuminate\Support\ServiceProvider;
use Kakaprodo\CustomData\Command\CustomDataGenerator;
use Kakaprodo\CustomData\Command\CustomActionGenerator;
use Kakaprodo\CustomData\Command\ConfigInstallGenerator;

class CustomDataServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/config/custom-data.php',
            'custom-data'
        );
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerCommands();

        $this->stackToPublish();
    }

    protected function registerCommands()
    {
        if (!$this->app->runningInConsole()) return;

        $this->commands([
            CustomActionGenerator::class,
            CustomDataGenerator::class,
            ConfigInstallGenerator::class
        ]);
    }


    public function stackToPublish()
    {
        $this->publishes([
            __DIR__ . '/config/custom-data.php' => config_path('custom-data.php'),
        ], 'custom-data-config');
    }
}
