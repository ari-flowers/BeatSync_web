<?php

use function Livewire\Volt\layout;

layout('layouts.app');

?>

<div class="p-6 max-w-7xl mx-auto">
    <h1 class="text-2xl font-bold">Welcome to BeatSync</h1>

    <div class="mt-6 p-6 bg-white shadow rounded-2xl border border-gray-200">
        <h2 class="text-xl font-semibold">🎧 Connected Services</h2>
        <p class="text-gray-500 mt-2">
            You haven’t connected any streaming services yet.
        </p>
        <x-button class="mt-4">Connect Spotify</x-button>
    </div>
</div>