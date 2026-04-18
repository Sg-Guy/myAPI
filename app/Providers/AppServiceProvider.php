<?php

namespace App\Providers;

use App\Notifications\NewUserNotification;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
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
        if (config('app.env') == 'production') {
            URL::forceScheme('https');
        };

        Event::listen(Registered::class, function (Registered $event) {
            // Remplacez par l'adresse email de l'administrateur
            Notification::route('mail', config('mail.admin_address', 'admin@example.com'))
                ->notify(new NewUserNotification($event->user));
        });
    }
}
