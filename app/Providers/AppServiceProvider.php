<?php

namespace App\Providers;

use App\Services\WhatsAppCloudApi;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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
        View::composer(['components.footer', 'home', 'about', 'contact', 'pos.receipt'], function ($view) {
            $view->with(
                'whatsappAdminNumber',
                app(WhatsAppCloudApi::class)->adminNumber()
            );
        });
    }
}
