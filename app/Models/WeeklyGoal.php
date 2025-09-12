<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property int $year
 * @property int $week_number 週番号 (1-53)
 * @property string|null $goal_this_week 今週の目標・総括
 * @property string|null $plan_next_week 来週の予定
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Database\Factories\WeeklyGoalFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WeeklyGoal newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WeeklyGoal newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WeeklyGoal query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WeeklyGoal whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WeeklyGoal whereGoalThisWeek($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WeeklyGoal whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WeeklyGoal wherePlanNextWeek($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WeeklyGoal whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WeeklyGoal whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WeeklyGoal whereWeekNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WeeklyGoal whereYear($value)
 * @mixin \Eloquent
 */
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
