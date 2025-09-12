<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Division;
use App\Models\Knowledge;
use Illuminate\Support\Facades\Hash;
use App\Models\DailyReport;
use App\Models\WeeklyGoal;
use Carbon\Carbon;


class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // --- 1. まず、全ての部署を先に作成する （開発、営業、その他）---
        $devDivision = Division::factory()->create(['name' => '開発部']);
        $salesDivision = Division::factory()->create(['name' => '営業部']);
        $otherDivisions = Division::factory(5)->create();

        // 作成した部署を一つのコレクションにまとめておくと便利
        $allDivisions = collect([$devDivision, $salesDivision])->merge($otherDivisions);

        // --- 2. 次に、テスト用の固定ユーザー（あなた）を作成する ---
        $adminUser = User::factory()->create([
            'name' => '美晴 (管理者)',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'division_id' => $devDivision->id, // 開発部に所属
            'is_admin' => true,
        ]);

        // --- 3. その後、ランダムな一般ユーザーを作成する ---
        // 作成したユーザーを変数に格納しておくのがポイント
        $generalUsers = User::factory(49)->create([
            'division_id' => $allDivisions->random()->id,
        ]);

        // --- 4. 最後に、作成したユーザーの中から投稿者を選んで、ナレッジを作成する ---
        // $adminUserと$generalUsersを結合して、全ユーザーの中からランダムに選ぶ
        $allUsers = collect([$adminUser])->merge($generalUsers);

        Knowledge::factory(100)->create([
            'user_id' => $allUsers->random()->id,
        ]);



        // --- 5. 全ユーザーの、日報と週目標を作成する ---
        //    Seederの$allUsers変数を再利用
        foreach ($allUsers as $user) {
            // --- 過去90日間の日付をループ ---
            for ($i = 0; $i < 90; $i++) {
                $date = Carbon::today()->subDays($i);

                // 70%の確率で、その日の日報を作成する (毎日全員が書くとは限らない)
                if (rand(0, 10) < 7) {
                    DailyReport::factory()->create([
                        'user_id' => $user->id,
                        'report_date' => $date->format('Y-m-d'),
                    ]);
                }

                // もし、その日が月曜日なら、その週の目標を作成する
                if ($date->isMonday()) {
                    WeeklyGoal::factory()->create([
                        'user_id' => $user->id,
                        'year' => $date->year,
                        'week_number' => $date->weekOfYear,
                    ]);
                }
            }
        }
        $this->call(ProjectSeeder::class);
        $this->call(EventSeeder::class);
    }
}
