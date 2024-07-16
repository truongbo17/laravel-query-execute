<?php

namespace Bo\LaravelQueryExecute;

use Illuminate\Support\ServiceProvider;

class LaravelQueryExecuteServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected bool $defer = false;

    private const KEY_PACKAGE = 'laravel-query-execute';

    /**
     * Where the route file lives, both inside the package and in the app (if overwritten).
     *
     * @var string
     */
    private string $routeFilePath = '/routes/query-execute.php';

    private string $migrationFilePath = '/database/migrations';

    private string $viewFilePath = '/resources/views';

    private string $langFilePath = '/resources/lang';

    private string $configFilePath = '/config/query-execute.php';

    private string $assetFilePath = '/assets';

    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->loadViewsFrom(dirname(__DIR__) . $this->viewFilePath, self::KEY_PACKAGE);

        $this->loadTranslationsFrom(dirname(__DIR__) . $this->langFilePath, self::KEY_PACKAGE);

        $this->loadMigrationsFrom(dirname(__DIR__) . $this->migrationFilePath);

        $this->mergeConfigFrom(dirname(__DIR__) . $this->configFilePath, self::KEY_PACKAGE);

        $this->publishes([
            dirname(__DIR__) . $this->configFilePath => config_path(self::KEY_PACKAGE . str_replace('config', '', $this->configFilePath)),
        ], 'config');

        $this->publishes([
            dirname(__DIR__) . $this->assetFilePath  => public_path(self::KEY_PACKAGE),
        ], 'assets');
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->setupRoutes();
    }


    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function setupRoutes(): void
    {
        $routeFilePathInUse = dirname(__DIR__) . $this->routeFilePath;
        $this->loadRoutesFrom($routeFilePathInUse);
    }
}
