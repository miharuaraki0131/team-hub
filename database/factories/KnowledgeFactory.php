<?php

namespace Database\Factories;

use App\Models\User; // [追加]
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str; // [追加]

class KnowledgeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(), // Userを自動で作って、そのIDを入れる
            'title' => fake()->realText(50), // 日本語のリアルな文章を50文字生成
            'body' => fake()->realText(800),
            'is_pinned' => fake()->boolean(10), // 10%の確率でtrueになる
            'published_at' => now(),
            'expired_at' => fake()->optional(0.3)->dateTimeBetween('now', '+3 months'), // 30%の確率で3ヶ月以内の失効日が設定される
        ];
    }
}
