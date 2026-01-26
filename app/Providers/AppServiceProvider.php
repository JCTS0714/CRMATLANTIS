<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
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
        View::composer('layouts.guest', function ($view) {
            $manifestPath = storage_path('app/public/settings/logo_mark.json');
            $manifestLogo = null;

            if (file_exists($manifestPath)) {
                $payload = json_decode(file_get_contents($manifestPath), true);
                if (is_array($payload) && !empty($payload['path']) && file_exists(public_path($payload['path']))) {
                    $manifestLogo = asset($payload['path']);
                }
            }

            $loginLogo = $manifestLogo
                ?: (file_exists(public_path('storage/settings/logo_mark.png'))
                    ? asset('storage/settings/logo_mark.png')
                    : asset('images/logo_alta_calidad.png'));

            $view->with('loginLogo', $loginLogo);
        });

        Gate::before(function ($user, string $ability) {
            return method_exists($user, 'hasRole') && $user->hasRole('admin') ? true : null;
        });
    }
}
