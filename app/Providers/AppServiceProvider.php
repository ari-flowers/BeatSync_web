<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use SocialiteProviders\Manager\SocialiteWasCalled;
use SocialiteProviders\Spotify\SpotifyExtendSocialite;
use Livewire\Volt\Volt;

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
        // Register Volt view paths
        Volt::mount([
            resource_path('views/livewire'),
            resource_path('views/pages'),
        ]);

        // Register the Spotify Socialite provider
        Event::listen(
            SocialiteWasCalled::class,
            [SpotifyExtendSocialite::class, 'handle']
        );
    }
}