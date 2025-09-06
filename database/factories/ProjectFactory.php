<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Project>
 */
class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

        return [
            'name' => 'TeamHub Ver.' . $this->faker->randomFloat(1, 2, 5) . ' 開発',
            'description' => $this->faker->realText(150),
            'created_by' => User::factory(), // プロジェクト作成者として、新しいUserを自動的に作成する
        ];
    }
}
