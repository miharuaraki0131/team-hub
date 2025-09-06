<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Division extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'notification_destination_id'
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
}
