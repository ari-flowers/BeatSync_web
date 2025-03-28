<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Volt;
use Laravel\Socialite\Facades\Socialite;

Route::get('/', function () {
    return view('welcome');
});

// Authenticated routes
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Volt::route('/dashboard', 'dashboard')->name('dashboard');
    Volt::route('/test', 'test-page')->name('test');

    // Check if the user is authenticated before redirecting to Spotify
    Route::get('/auth/spotify', function () {
        if (!Auth::check()) {
            return redirect('/login');  // Redirect to login if the user isn't authenticated
        }
        
        return Socialite::driver('spotify')->scopes([
            'user-read-private',
            'user-read-email',
            'playlist-read-private',
            'user-library-read',
        ])->redirect();
    })->name('spotify.redirect');

    Route::get('/auth/spotify/callback', function () {
        try {
            $spotifyUser = Socialite::driver('spotify')->user();
        } catch (\Exception $e) {
            dd('❌ Spotify auth failed:', $e->getMessage());
        }

        $user = Auth::user();

        if (!$user) {
            dd('❌ No authenticated Laravel user found during callback');
        }

        $user->spotify_id = $spotifyUser->getId();
        $user->spotify_token = $spotifyUser->token;
        $user->spotify_refresh_token = $spotifyUser->refreshToken;
        $user->spotify_token_expires = now()->addSeconds($spotifyUser->expiresIn);
        $user->save();

        return redirect('/dashboard')->with('success', '✅ Spotify connected!');
    });

    Volt::route('/volt-test', 'volt-test')->name('volt.test');
});