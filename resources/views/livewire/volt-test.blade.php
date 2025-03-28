<?php

use function Livewire\Volt\{layout, hydrate, state};

layout('layouts.app');

state(['message' => 'initial']);

hydrate(function () {
    logger()->info('✅ Volt hydrate() ran');
    state()->message = '✅ hydrate() ran successfully!';
});
?>

<div class="p-6">
    <h1 class="text-2xl font-bold">🧪 Volt Test Page</h1>
    <p>🔧 Message: <strong>{{ $message }}</strong></p>
</div>