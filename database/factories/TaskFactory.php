<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    public function definition(): array
    {
        // リアルな日付を生成
        $plannedStart = $this->faker->dateTimeBetween('-1 week', '+1 week');
        $plannedEnd = Carbon::instance($plannedStart)->addDays($this->faker->numberBetween(1, 7));

        return [
            'project_id' => Project::factory(), // 所属プロジェクトも自動作成
            'user_id' => User::factory(),       // 担当者も自動作成
            'parent_id' => null,                // デフォルトでは親タスクなし
            'title' => $this->faker->realText(30),
            'description' => $this->faker->optional(0.7)->realText(150), // 70%の確率で詳細が入る
            'status' => $this->faker->randomElement(['todo', 'in_progress', 'done']),
            'planned_start_date' => $plannedStart,
            'planned_end_date' => $plannedEnd,
            'actual_start_date' => null, // 実績は、最初はnullにしておく
            'actual_end_date' => null,
            'planned_effort' => $this->faker->randomElement([4, 8, 16, 24, 40]), // 4時間, 1日, 2日...のような工数
            'actual_effort' => null,
            'position' => 0,
            'created_by' => User::factory(),   // 作成者も自動作成
        ];
    }
}
