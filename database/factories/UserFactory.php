<?php

namespace Database\Factories;

use App\Models\Division; // [追加]
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'division_id' => Division::factory(), // [追加] Divisionを自動で作って、そのIDを入れる
            'is_admin' => false, // [追加] デフォルトは非管理者
        ];
    }

    /**
     * [追加] メールアドレスが未認証の状態を示す
     *
     * @return static
     */
    public function unverified(): static
    {
        return $this->state(fn(array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
