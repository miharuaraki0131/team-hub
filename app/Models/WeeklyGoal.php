<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeeklyGoal extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'year',
        'week_number',
        'goal_this_week',
        'plan_next_week',
    ];
}
