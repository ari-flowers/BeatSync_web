<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\User;

class RefreshSpotifyTokens extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'app:refresh-spotify-tokens';

    /**
     * The console command description.
     */
    protected $description = 'Refresh Spotify access tokens for users whose tokens are about to expire.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $users = User::whereNotNull('spotify_refresh_token')
            ->where('spotify_token_expires', '<=', now()->addMinutes(5))
            ->get();

        foreach ($users as $user) {
            $response = Http::asForm()->post('https://accounts.spotify.com/api/token', [
                'grant_type' => 'refresh_token',
                'refresh_token' => $user->spotify_refresh_token,
                'client_id' => config('services.spotify.client_id'),
                'client_secret' => config('services.spotify.client_secret'),
            ]);

            if ($response->ok()) {
                $data = $response->json();

                $user->spotify_token = $data['access_token'];
                $user->spotify_token_expires = now()->addSeconds($data['expires_in']);

                if (isset($data['refresh_token'])) {
                    $user->spotify_refresh_token = $data['refresh_token'];
                }

                $user->save();

                $this->info("✅ Refreshed Spotify token for {$user->email}");
            } else {
                $this->error("❌ Failed to refresh token for {$user->email}: " . $response->body());
            }
        }

        $this->info('🎧 Done refreshing tokens');
    }
}