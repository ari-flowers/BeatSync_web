<?php

use function Livewire\Volt\layout;

layout('layouts.app');

?>

<div class="p-6 max-w-7xl mx-auto">
    <h1 class="text-2xl font-bold">Welcome to BeatSync</h1>

    <div class="mt-2 text-sm text-gray-600">
        <p>👤 Logged in as: <strong>{{ auth()->user()->name }}</strong></p>
        <p>📧 Email: <strong>{{ auth()->user()->email }}</strong></p>
        <p>🆔 Spotify ID: <strong>{{ auth()->user()->spotify_id ?? 'None' }}</strong></p>
    </div>

    <div class="mt-6 p-6 bg-white shadow rounded-2xl border border-gray-200">
        <h2 class="text-xl font-semibold">🎧 Connected Services</h2>

        @if (auth()->user()->spotify_id)
            <p class="text-green-600 font-semibold mt-4">✅ Spotify Connected</p>
        @else
            <a
                href="{{ route('spotify.redirect') }}"
                class="inline-block mt-4 px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition"
            >
                Connect Spotify
            </a>
        @endif
    </div>
</div>