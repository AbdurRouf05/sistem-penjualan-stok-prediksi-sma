<?php

namespace Modules\Forecast\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    protected $moduleNamespace = 'Modules\Forecast\Http\Controllers';

    public function map()
    {
        Route::middleware('web')
            ->namespace($this->moduleNamespace)
            ->group(module_path('Forecast', '/Routes/web.php'));
    }
}
