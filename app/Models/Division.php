<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Division extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'logo_path',
    ];


    /**
     * Users belonging to this division.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function notificationDestinations(): HasMany
    {
        return $this->hasMany(NotificationDestination::class);
    }

    public function getLogoUrlAttribute(): ?string
    {
        if ($this->logo_path) {
            return Storage::url($this->logo_path);
        }

        return asset('images/team-hub-logo.png');
    }
}
