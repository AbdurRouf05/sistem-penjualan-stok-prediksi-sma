<?php

namespace Modules\Order\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    protected $moduleNamespace = 'Modules\Order\Http\Controllers';

    public function map()
    {
        Route::middleware('web')
            ->namespace($this->moduleNamespace)
            ->group(module_path('Order', '/Routes/web.php'));
    }
}
