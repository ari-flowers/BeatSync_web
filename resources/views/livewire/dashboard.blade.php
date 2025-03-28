<?php

use function Livewire\Volt\{state, layout, mount};

layout('layouts.app');

state(['message' => 'Not set']);

mount(function () {
    state()->message = '✅ mount() is working!';
});
?>

<div class="p-6 max-w-4xl mx-auto">
    <h1 class="text-2xl font-bold">Mount Test</h1>

    <div class="mt-4 p-4 bg-white shadow rounded border">
        <p class="text-lg text-blue-600 font-semibold">{{ $message }}</p>
    </div>
</div>