<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class DailyReportFactory extends Factory
{
    public function definition(): array
    {
        // 過去90日以内の、ランダムな日付を生成
        $reportDate = fake()->dateTimeBetween('-90 days', 'now');

        return [
            'user_id' => User::factory(),
            'report_date' => $reportDate,
            'summary_today' => '・' . fake()->realText(100) . "\n" . '・' . fake()->realText(100),
            'discrepancy' => fake()->optional(0.7)->realText(80), // 70%の確率でデータが入る
            'summary_tomorrow' => '・' . fake()->realText(100),
            'issues_thoughts' => fake()->optional(0.5)->realText(120), // 50%の確率でデータが入る
        ];
    }
}
