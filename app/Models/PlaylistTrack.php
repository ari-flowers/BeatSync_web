<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlaylistTrack extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'playlist_id',
        'track_id',
        'position',
        'added_by',
        'added_at',
        'is_local',
    ];
    
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'position' => 'integer',
        'added_at' => 'datetime',
        'is_local' => 'boolean',
    ];
    
    /**
     * Get the playlist that the track belongs to.
     */
    public function playlist(): BelongsTo
    {
        return $this->belongsTo(Playlist::class);
    }
    
    /**
     * Get the track in the playlist.
     */
    public function track(): BelongsTo
    {
        return $this->belongsTo(Track::class);
    }
}
