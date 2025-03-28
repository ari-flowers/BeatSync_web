<?php

use function Livewire\Volt\{state, layout, mount};
use App\Services\SpotifyService;
use App\Models\Playlist;

layout('layouts.app');

state([
    'spotifyConnected' => false,
    'spotifyPlaylists' => [],
    'dbPlaylists' => [],
    'isImporting' => false,
    'importMessage' => '',
    'importStatus' => '',
]);

mount(function () {
    $user = auth()->user();
    
    // Check if user has Spotify connected
    $spotifyConnected = $user->spotify_token !== null;
    state()->spotifyConnected = $spotifyConnected;
    
    // Fetch playlists from database
    state()->dbPlaylists = $user->playlists()
        ->where('service', 'spotify')
        ->with(['tracks' => function($query) {
            $query->select(['tracks.id', 'title', 'artist', 'image_url']);
        }])
        ->get();
    
    // If connected to Spotify, fetch available playlists
    if ($spotifyConnected) {
        $spotifyService = new SpotifyService();
        state()->spotifyPlaylists = $spotifyService->getUserPlaylists($user);
    }
});

// Import a single playlist
function importPlaylist($playlistId) {
    // Update state variables
    $this->set('isImporting', true);
    $this->set('importMessage', 'Importing playlist...');
    $this->set('importStatus', 'processing');
    
    $user = auth()->user();
    $spotifyService = new SpotifyService();
    
    // Find the playlist in the fetched Spotify playlists
    $spotifyPlaylist = collect($this->spotifyPlaylists)->firstWhere('id', $playlistId);
    
    if (!$spotifyPlaylist) {
        $this->set('importMessage', 'Playlist not found');
        $this->set('importStatus', 'error');
        $this->set('isImporting', false);
        return;
    }
    
    // Import the playlist
    $playlist = $spotifyService->importPlaylist($user, $spotifyPlaylist);
    
    if ($playlist) {
        $this->set('importMessage', 'Playlist imported successfully!');
        $this->set('importStatus', 'success');
        
        // Refresh the database playlists
        $dbPlaylists = $user->playlists()
            ->where('service', 'spotify')
            ->with(['tracks' => function($query) {
                $query->select(['tracks.id', 'title', 'artist', 'image_url']);
            }])
            ->get();
        $this->set('dbPlaylists', $dbPlaylists);
    } else {
        $this->set('importMessage', 'Failed to import playlist');
        $this->set('importStatus', 'error');
    }
    
    $this->set('isImporting', false);
}

// Import all playlists
function importAllPlaylists() {
    // Update state variables
    $this->set('isImporting', true);
    $this->set('importMessage', 'Importing all playlists...');
    $this->set('importStatus', 'processing');
    
    $user = auth()->user();
    $spotifyService = new SpotifyService();
    
    // Import all playlists
    $count = $spotifyService->importAllPlaylists($user);
    
    if ($count > 0) {
        $this->set('importMessage', "Successfully imported {$count} playlists!");
        $this->set('importStatus', 'success');
        
        // Refresh the database playlists
        $dbPlaylists = $user->playlists()
            ->where('service', 'spotify')
            ->with(['tracks' => function($query) {
                $query->select(['tracks.id', 'title', 'artist', 'image_url']);
            }])
            ->get();
        $this->set('dbPlaylists', $dbPlaylists);
    } else {
        $this->set('importMessage', 'No playlists were imported');
        $this->set('importStatus', 'warning');
    }
    
    $this->set('isImporting', false);
}
?>

<div class="p-6 max-w-6xl mx-auto">
    <h1 class="text-2xl font-bold mb-6">BeatSync Dashboard</h1>
    
    <!-- Spotify Connection Status -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold">Spotify Integration</h2>
            
            @if ($spotifyConnected)
                <span class="text-sm text-green-600 bg-green-100 px-3 py-1 rounded-full">
                    ✓ Connected
                </span>
            @else
                <span class="text-sm text-red-600 bg-red-100 px-3 py-1 rounded-full">
                    Not Connected
                </span>
            @endif
        </div>
        
        @if (!$spotifyConnected)
            <div class="mt-4">
                <p class="mb-4">Connect your Spotify account to view and manage your playlists.</p>
                <a href="{{ route('spotify.redirect') }}" 
                   class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-bold rounded-md">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.48.659.301 1.02zm1.44-3.3c-.301.42-.841.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141C9.6 9.9 15 10.561 18.72 12.84c.361.181.54.78.241 1.2zm.12-3.36C15.24 8.4 8.82 8.16 5.16 9.301c-.6.179-1.2-.181-1.38-.721-.18-.601.18-1.2.72-1.381 4.26-1.26 11.28-1.02 15.721 1.621.539.3.719 1.02.419 1.56-.299.421-1.02.599-1.559.3z"/>
                    </svg>
                    Connect Spotify
                </a>
            </div>
        @else
            <div class="mt-4">
                <p class="mb-4">Import your Spotify playlists to manage them with BeatSync.</p>
                
                @if ($isImporting)
                    <div class="flex items-center mb-4">
                        <svg class="animate-spin h-5 w-5 mr-2 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>{{ $importMessage }}</span>
                    </div>
                @else
                    @if ($importMessage)
                        <div class="mb-4 p-3 rounded-md {{ $importStatus === 'success' ? 'bg-green-100 text-green-700' : ($importStatus === 'error' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700') }}">
                            {{ $importMessage }}
                        </div>
                    @endif
                    
                    <button wire:click="importAllPlaylists" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-md">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                        </svg>
                        Import All Playlists
                    </button>
                @endif
            </div>
        @endif
    </div>
    
    <!-- Imported Playlists Section -->
    @if (count($dbPlaylists) > 0)
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">Your Imported Playlists</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach ($dbPlaylists as $playlist)
                    <div class="border rounded-lg overflow-hidden bg-gray-50 hover:shadow-md transition-shadow">
                        <div class="h-40 bg-gray-200 overflow-hidden">
                            @if ($playlist->image_url)
                                <img src="{{ $playlist->image_url }}" alt="{{ $playlist->name }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center bg-gray-300">
                                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"></path>
                                    </svg>
                                </div>
                            @endif
                        </div>
                        <div class="p-4">
                            <h3 class="font-semibold text-gray-800 truncate">{{ $playlist->name }}</h3>
                            <p class="text-sm text-gray-600 mt-1">{{ $playlist->tracks->count() }} tracks</p>
                            <div class="mt-3 flex justify-between">
                                <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded">
                                    {{ ucfirst($playlist->service) }}
                                </span>
                                @if ($playlist->is_public)
                                    <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded">
                                        Public
                                    </span>
                                @else
                                    <span class="text-xs bg-gray-100 text-gray-800 px-2 py-1 rounded">
                                        Private
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
    
    <!-- Available Spotify Playlists Section -->
    @if ($spotifyConnected && count($spotifyPlaylists) > 0)
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">Available Spotify Playlists</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach ($spotifyPlaylists as $playlist)
                    <div class="border rounded-lg overflow-hidden bg-gray-50 hover:shadow-md transition-shadow">
                        <div class="h-40 bg-gray-200 overflow-hidden">
                            @if (!empty($playlist['images'][0]['url'] ?? null))
                                <img src="{{ $playlist['images'][0]['url'] }}" alt="{{ $playlist['name'] }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center bg-gray-300">
                                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"></path>
                                    </svg>
                                </div>
                            @endif
                        </div>
                        <div class="p-4">
                            <h3 class="font-semibold text-gray-800 truncate">{{ $playlist['name'] }}</h3>
                            <p class="text-sm text-gray-600 mt-1">{{ $playlist['tracks']['total'] ?? 0 }} tracks</p>
                            <div class="mt-3 flex justify-between items-center">
                                @if ($playlist['public'] ?? false)
                                    <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded">
                                        Public
                                    </span>
                                @else
                                    <span class="text-xs bg-gray-100 text-gray-800 px-2 py-1 rounded">
                                        Private
                                    </span>
                                @endif
                                
                                @if (!$isImporting)
                                    <button wire:click="importPlaylist('{{ $playlist['id'] }}')" class="text-xs bg-blue-600 hover:bg-blue-700 text-white px-2 py-1 rounded">
                                        Import
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>