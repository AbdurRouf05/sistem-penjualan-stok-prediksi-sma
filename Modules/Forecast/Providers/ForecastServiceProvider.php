<?php

namespace Modules\Forecast\Providers;

use Illuminate\Support\ServiceProvider;

class ForecastServiceProvider extends ServiceProvider
{
    protected $moduleName = 'Forecast';

    protected $moduleNameLower = 'forecast';

    public function boot()
    {
        $this->mergeConfigFrom(module_path($this->moduleName, 'Config/config.php'), $this->moduleNameLower);
        $this->loadViewsFrom(module_path($this->moduleName, 'Resources/views'), $this->moduleNameLower);
        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));
    }

    public function register()
    {
        $this->app->register(RouteServiceProvider::class);
    }
}
