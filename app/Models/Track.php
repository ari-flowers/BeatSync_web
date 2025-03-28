<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Track extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'artist',
        'album',
        'isrc',
        'duration_ms',
        'year',
        'genre',
        'service_data',
        'local_path',
        'preview_url',
        'image_url',
    ];
    
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'duration_ms' => 'integer',
        'year' => 'integer',
        'service_data' => 'array',
    ];
    
    /**
     * Get the playlists containing this track.
     */
    public function playlists(): BelongsToMany
    {
        return $this->belongsToMany(Playlist::class, 'playlist_tracks')
            ->withPivot(['position', 'added_by', 'added_at', 'is_local'])
            ->withTimestamps();
    }
    
    /**
     * Get the service ID for a specific service (Spotify, Tidal, etc.)
     */
    public function getServiceId(string $service): ?string
    {
        if (!$this->service_data) {
            return null;
        }
        
        $serviceData = $this->service_data;
        return $serviceData[$service]['id'] ?? null;
    }
    
    /**
     * Add service data to the track
     */
    public function addServiceData(string $service, array $data): self
    {
        $serviceData = $this->service_data ?? [];
        $serviceData[$service] = $data;
        
        $this->service_data = $serviceData;
        
        return $this;
    }
}
