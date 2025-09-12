<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Event;
use Carbon\Carbon;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 既存の全ユーザーを取得
        $users = User::all();

        // 各ユーザーに対してループ処理
        foreach ($users as $user) {
            // 1ユーザーあたり3〜10件のランダムな数のイベントを作成
            $numberOfEvents = rand(3, 10);

            for ($i = 0; $i < $numberOfEvents; $i++) {
                // -30日〜+30日の範囲でランダムな日付を生成
                $randomDate = Carbon::now()->addDays(rand(-30, 30));

                // 終日フラグをランダムに決定 (30%の確率でtrue)
                $isAllDay = (rand(1, 10) <= 3);

                // 開始日時と終了日時を設定
                if ($isAllDay) {
                    $startDateTime = $randomDate->copy()->startOfDay();
                    $endDateTime = $randomDate->copy()->startOfDay();
                } else {
                    $startHour = rand(9, 17); // 9時〜17時の間で開始
                    $startDateTime = $randomDate->copy()->hour($startHour)->minute(rand(0, 1) * 30); // 0分 or 30分
                    $endDateTime = $startDateTime->copy()->addHours(rand(1, 3)); // 1〜3時間後を終了時刻とする
                }

                Event::create([
                    'user_id' => $user->id,
                    'title' => 'サンプル予定 ' . ($i + 1),
                    'body' => 'これは' . $user->name . 'のサンプル予定の詳細です。',
                    'start_datetime' => $startDateTime,
                    'end_datetime' => $endDateTime,
                    'is_all_day' => $isAllDay,
                    'category' => ['会議', '個人', 'チーム', null][array_rand(['会議', '個人', 'チーム', null])],
                    'visibility' => ['public', 'private'][array_rand(['public', 'private'])],
                    'color' => ['#3B82F6', '#EF4444', '#10B981', '#F59E0B'][array_rand(['#3B82F6', '#EF4444', '#10B981', '#F59E0B'])],
                ]);
            }
        }
    }
}