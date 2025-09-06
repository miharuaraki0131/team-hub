<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = $this->faker->dateTimeBetween('-1 month', '+1 month');
        $end = Carbon::instance($start)->addHours($this->faker->numberBetween(1, 3));

        return [
            'user_id' => User::factory(),
            'title' => $this->faker->sentence(3), // 3単語の文
            'body' => $this->faker->optional(0.7)->paragraph(), // 70%の確率で段落が入る
            'start_datetime' => $start,
            'end_datetime' => $end,
            'is_all_day' => false,
            'category' => $this->faker->optional()->word(), // たまにカテゴリが入る
            'visibility' => $this->faker->randomElement(['public', 'private']), // publicかprivateをランダムに選択
            'color' => $this->faker->hexColor(), // #xxxxxx 形式のランダムな色
        ];
    }
}
