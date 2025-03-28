<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Track;
use App\Models\Playlist;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create a test user
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
        
        // Create some sample tracks
        $tracks = [
            [
                'title' => 'Bohemian Rhapsody',
                'artist' => 'Queen',
                'album' => 'A Night at the Opera',
                'duration_ms' => 354947,
                'year' => 1975,
                'genre' => 'Rock',
                'image_url' => 'https://i.scdn.co/image/ab67616d0000b273e8b066f70c206551210d902b',
            ],
            [
                'title' => 'Thriller',
                'artist' => 'Michael Jackson',
                'album' => 'Thriller',
                'duration_ms' => 358133,
                'year' => 1982,
                'genre' => 'Pop',
                'image_url' => 'https://i.scdn.co/image/ab67616d0000b27351c02a77d09dfcd53c8676d0',
            ],
            [
                'title' => 'Billie Jean',
                'artist' => 'Michael Jackson',
                'album' => 'Thriller',
                'duration_ms' => 292880,
                'year' => 1982,
                'genre' => 'Pop',
                'image_url' => 'https://i.scdn.co/image/ab67616d0000b27351c02a77d09dfcd53c8676d0',
            ],
            [
                'title' => 'Imagine',
                'artist' => 'John Lennon',
                'album' => 'Imagine',
                'duration_ms' => 183000,
                'year' => 1971,
                'genre' => 'Rock',
                'image_url' => 'https://i.scdn.co/image/ab67616d0000b273d750ac0a2f75eedbf433b199',
            ],
            [
                'title' => 'Hotel California',
                'artist' => 'Eagles',
                'album' => 'Hotel California',
                'duration_ms' => 390200,
                'year' => 1976,
                'genre' => 'Rock',
                'image_url' => 'https://i.scdn.co/image/ab67616d0000b273d9e190f35d8153b8a2fc24ab',
            ],
        ];
        
        foreach ($tracks as $trackData) {
            Track::create($trackData);
        }
        
        // Create a sample playlist
        $playlist = Playlist::create([
            'user_id' => $user->id,
            'name' => 'My Rock Classics',
            'description' => 'My favorite rock songs for testing',
            'service' => 'spotify',
            'service_id' => 'test_playlist_id',
            'image_url' => 'https://i.scdn.co/image/ab67616d0000b273e8b066f70c206551210d902b',
            'is_public' => true,
            'is_collaborative' => false,
        ]);
        
        // Add tracks to the playlist
        $trackIds = Track::whereIn('title', ['Bohemian Rhapsody', 'Hotel California', 'Imagine'])->pluck('id');
        
        foreach ($trackIds as $index => $trackId) {
            $playlist->tracks()->attach($trackId, [
                'position' => $index,
                'added_by' => 'seeder',
                'added_at' => now(),
                'is_local' => false,
            ]);
        }
        
        // Create a second playlist
        $playlist2 = Playlist::create([
            'user_id' => $user->id,
            'name' => 'Michael Jackson Hits',
            'description' => 'The best of MJ',
            'service' => 'spotify',
            'service_id' => 'test_playlist_id_2',
            'image_url' => 'https://i.scdn.co/image/ab67616d0000b27351c02a77d09dfcd53c8676d0',
            'is_public' => true,
            'is_collaborative' => false,
        ]);
        
        // Add MJ tracks to the playlist
        $mjTrackIds = Track::where('artist', 'Michael Jackson')->pluck('id');
        
        foreach ($mjTrackIds as $index => $trackId) {
            $playlist2->tracks()->attach($trackId, [
                'position' => $index,
                'added_by' => 'seeder',
                'added_at' => now(),
                'is_local' => false,
            ]);
        }
    }
}
