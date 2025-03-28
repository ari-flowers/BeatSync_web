<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\User;

class SpotifyService
{
    public function getUserPlaylists(User $user)
    {
        $accessToken = $user->spotify_token;

        $response = Http::withToken($accessToken)
            ->get('https://api.spotify.com/v1/me/playlists', [
                'limit' => 20,
            ]);

        // ✅ Log the raw response for debugging
        logger()->info('Spotify playlist response', [
            'status' => $response->status(),
            'body' => $response->json(),
        ]);

        if ($response->failed()) {
            return collect(); // Return empty collection on failure
        }

        return collect($response->json()['items'] ?? []);
    }
}