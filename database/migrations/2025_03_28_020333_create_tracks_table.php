<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tracks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('artist');
            $table->string('album')->nullable();
            $table->string('isrc')->nullable()->index(); // International Standard Recording Code
            $table->integer('duration_ms')->nullable();
            $table->integer('year')->nullable();
            $table->string('genre')->nullable();
            $table->json('service_data')->nullable(); // Stores IDs and metadata from different services
            $table->string('local_path')->nullable(); // Path to local file if available
            $table->string('preview_url')->nullable();
            $table->string('image_url')->nullable();
            $table->timestamps();
            
            // Create a composite key using normalized title and artist for matching
            $table->index(['title', 'artist'], 'track_search_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tracks');
    }
};
