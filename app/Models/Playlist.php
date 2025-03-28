<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Playlist extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'description',
        'service',
        'service_id',
        'image_url',
        'is_public',
        'is_collaborative',
        'metadata',
    ];
    
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_public' => 'boolean',
        'is_collaborative' => 'boolean',
        'metadata' => 'array',
    ];
    
    /**
     * Get the user that owns the playlist.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Get the tracks in the playlist.
     */
    public function tracks(): BelongsToMany
    {
        return $this->belongsToMany(Track::class, 'playlist_tracks')
            ->withPivot(['position', 'added_by', 'added_at', 'is_local'])
            ->withTimestamps()
            ->orderBy('position');
    }
    
    /**
     * Scope a query to only include playlists from a specific service.
     */
    public function scopeService($query, $service)
    {
        return $query->where('service', $service);
    }
}
