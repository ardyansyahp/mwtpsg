<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Carbon\Carbon;
use App\Models\TFinishGoodIn;
use App\Models\TFinishGoodOut;
use App\Observers\FinishGoodInObserver;
use App\Observers\FinishGoodOutObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Force Session Config for Single Sign-On
        config(['session.domain' => null]);
        config(['session.path' => '/']);
        config(['session.cookie' => 'mwtpsg_session']);

        // Set default timezone untuk Carbon ke Asia/Jakarta
        Carbon::setLocale('id');
        date_default_timezone_set('Asia/Jakarta');

    }
}
