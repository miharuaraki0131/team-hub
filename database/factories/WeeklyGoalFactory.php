<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class WeeklyGoalFactory extends Factory
{
    public function definition(): array
    {
        // 過去12週以内の、ランダムな日付を取得
        $date = Carbon::instance(fake()->dateTimeBetween('-12 weeks', 'now'));

        return [
            'user_id' => User::factory(),
            'year' => $date->year,
            'week_number' => $date->weekOfYear,
            'goal_this_week' => '【目標】' . fake()->realText(150),
            'plan_next_week' => '【予定】' . fake()->realText(150),
        ];
    }
}
