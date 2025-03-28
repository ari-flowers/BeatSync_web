<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Playlist;
use App\Models\Track;

class SpotifyService
{
    /**
     * Get user playlists from Spotify API
     */
    public function getUserPlaylists(User $user)
    {
        if (!$user->hasSpotifyConnected() || $user->needsTokenRefresh()) {
            Log::warning('Spotify token missing or expired for user', ['user_id' => $user->id]);
            return collect();
        }
        
        try {
            $accessToken = $user->spotify_token;
    
            $response = Http::withToken($accessToken)
                ->get('https://api.spotify.com/v1/me/playlists', [
                    'limit' => 50,
                ]);
    
            if ($response->failed()) {
                Log::error('Failed to fetch Spotify playlists', [
                    'status' => $response->status(),
                    'error' => $response->body(),
                    'user_id' => $user->id,
                ]);
                return collect(); 
            }
    
            return collect($response->json()['items'] ?? []);
        } catch (\Exception $e) {
            Log::error('Exception fetching Spotify playlists', [
                'message' => $e->getMessage(),
                'user_id' => $user->id,
            ]);
            return collect();
        }
    }
    
    /**
     * Import a Spotify playlist into the database
     */
    public function importPlaylist(User $user, array $spotifyPlaylist): ?Playlist
    {
        try {
            // Create or update the playlist
            $playlist = Playlist::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'service' => 'spotify',
                    'service_id' => $spotifyPlaylist['id'],
                ],
                [
                    'name' => $spotifyPlaylist['name'],
                    'description' => $spotifyPlaylist['description'] ?? null,
                    'image_url' => $spotifyPlaylist['images'][0]['url'] ?? null,
                    'is_public' => $spotifyPlaylist['public'] ?? false,
                    'is_collaborative' => $spotifyPlaylist['collaborative'] ?? false,
                    'metadata' => [
                        'spotify_href' => $spotifyPlaylist['href'] ?? null,
                        'owner' => $spotifyPlaylist['owner']['display_name'] ?? null,
                    ],
                ]
            );
            
            // Import the tracks from Spotify API
            $this->importPlaylistTracks($user, $playlist, $spotifyPlaylist['tracks']['href']);
            
            return $playlist;
        } catch (\Exception $e) {
            Log::error('Failed to import Spotify playlist', [
                'message' => $e->getMessage(),
                'playlist_id' => $spotifyPlaylist['id'] ?? 'unknown',
                'user_id' => $user->id,
            ]);
            return null;
        }
    }
    
    /**
     * Import tracks from a Spotify playlist
     */
    private function importPlaylistTracks(User $user, Playlist $playlist, string $tracksEndpoint): void
    {
        if (!$user->hasSpotifyConnected()) {
            return;
        }
        
        try {
            $accessToken = $user->spotify_token;
            $response = Http::withToken($accessToken)->get($tracksEndpoint, [
                'limit' => 100,
            ]);
            
            if ($response->failed()) {
                Log::error('Failed to fetch Spotify playlist tracks', [
                    'status' => $response->status(),
                    'error' => $response->body(),
                    'playlist_id' => $playlist->id,
                ]);
                return;
            }
            
            $trackItems = $response->json()['items'] ?? [];
            $position = 0;
            
            foreach ($trackItems as $item) {
                // Skip null tracks
                if (!isset($item['track']) || $item['track'] === null) {
                    continue;
                }
                
                $spotifyTrack = $item['track'];
                
                // Skip local files for now (we'll handle these with the agent later)
                if ($spotifyTrack['is_local'] ?? false) {
                    continue;
                }
                
                // Create or update the track
                $track = $this->findOrCreateTrack($spotifyTrack);
                
                if ($track) {
                    // Add to playlist with position
                    $playlist->tracks()->syncWithoutDetaching([
                        $track->id => [
                            'position' => $position,
                            'added_by' => $item['added_by']['id'] ?? 'spotify',
                            'added_at' => $item['added_at'] ?? now(),
                            'is_local' => false,
                        ]
                    ]);
                    
                    $position++;
                }
            }
            
            // Handle pagination - if there are more tracks, fetch them
            if (isset($response->json()['next']) && $response->json()['next']) {
                $this->importPlaylistTracks($user, $playlist, $response->json()['next']);
            }
        } catch (\Exception $e) {
            Log::error('Exception importing Spotify playlist tracks', [
                'message' => $e->getMessage(),
                'playlist_id' => $playlist->id,
            ]);
        }
    }
    
    /**
     * Find or create a track from Spotify data
     */
    private function findOrCreateTrack(array $spotifyTrack): ?Track
    {
        try {
            // Try to find by ISRC first (most accurate)
            $isrc = $spotifyTrack['external_ids']['isrc'] ?? null;
            $track = null;
            
            if ($isrc) {
                $track = Track::where('isrc', $isrc)->first();
            }
            
            // If not found by ISRC, try to find by title and artist
            if (!$track) {
                $artistName = $spotifyTrack['artists'][0]['name'] ?? 'Unknown Artist';
                
                $track = Track::where('title', $spotifyTrack['name'])
                    ->where('artist', $artistName)
                    ->first();
            }
            
            // If still not found, create a new track
            if (!$track) {
                $track = new Track();
                $track->title = $spotifyTrack['name'];
                $track->artist = $spotifyTrack['artists'][0]['name'] ?? 'Unknown Artist';
                $track->album = $spotifyTrack['album']['name'] ?? null;
                $track->isrc = $isrc;
                $track->duration_ms = $spotifyTrack['duration_ms'] ?? null;
                
                // Extract year from release date if available
                if (isset($spotifyTrack['album']['release_date'])) {
                    $releaseDate = $spotifyTrack['album']['release_date'];
                    if (strlen($releaseDate) >= 4) {
                        $track->year = (int) substr($releaseDate, 0, 4);
                    }
                }
                
                // Set album image if available
                if (isset($spotifyTrack['album']['images'][0]['url'])) {
                    $track->image_url = $spotifyTrack['album']['images'][0]['url'];
                }
                
                // Set preview URL if available
                $track->preview_url = $spotifyTrack['preview_url'] ?? null;
                
                $track->save();
            }
            
            // Always update the service data with the latest from Spotify
            $track->addServiceData('spotify', [
                'id' => $spotifyTrack['id'],
                'uri' => $spotifyTrack['uri'],
                'url' => $spotifyTrack['external_urls']['spotify'] ?? null,
                'popularity' => $spotifyTrack['popularity'] ?? null,
                'explicit' => $spotifyTrack['explicit'] ?? false,
                'artists' => collect($spotifyTrack['artists'])->map(function ($artist) {
                    return [
                        'id' => $artist['id'],
                        'name' => $artist['name'],
                    ];
                })->toArray(),
            ]);
            
            $track->save();
            
            return $track;
        } catch (\Exception $e) {
            Log::error('Exception creating track from Spotify data', [
                'message' => $e->getMessage(),
                'track_id' => $spotifyTrack['id'] ?? 'unknown',
            ]);
            return null;
        }
    }
    
    /**
     * Import all user playlists from Spotify
     */
    public function importAllPlaylists(User $user): int
    {
        $playlists = $this->getUserPlaylists($user);
        $count = 0;
        
        foreach ($playlists as $spotifyPlaylist) {
            if ($this->importPlaylist($user, $spotifyPlaylist)) {
                $count++;
            }
        }
        
        return $count;
    }
}