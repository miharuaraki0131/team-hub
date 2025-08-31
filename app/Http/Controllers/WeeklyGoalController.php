<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\WeeklyGoal;
use App\Models\User;
use Carbon\Carbon;

class WeeklyGoalController extends Controller
{
    /**
     * 指定された「ユーザー」の、指定された週の目標の編集画面を表示する
     */
    // ↓↓↓ メソッドの引数を、ルートに合わせて変更する ↓↓↓
    public function edit(User $user, int $year, int $week_number)
    {
        // --- 認可 ---
        // 表示しようとしている目標が、ログインユーザーのものでなければ403エラー
        if ($user->id !== Auth::id()) {
            abort(403, 'アクセス権がありません。');
        }

        // データベースから目標を取得するか、なければ新しいインスタンスを作成
        $weeklyGoal = WeeklyGoal::firstOrNew([
            'user_id' => $user->id,
            'year' => $year,
            'week_number' => $week_number,
        ]);

        // 週の開始日と終了日を計算
        $date = Carbon::now()->setISODate($year, $week_number);
        $startOfWeek = $date->copy()->startOfWeek(Carbon::MONDAY);
        $endOfWeek = $date->copy()->endOfWeek(Carbon::FRIDAY);

        return view('weekly-goals.edit', [
            'user' => $user, // ← ビューに$userを渡すのを忘れずに！
            'weeklyGoal' => $weeklyGoal,
            'year' => $year,
            'week_number' => $week_number,
            'startOfWeek' => $startOfWeek,
            'endOfWeek' => $endOfWeek,
        ]);
    }


    /**
     * 週の目標を保存または更新する
     */
    // ↓↓↓ こちらの引数も、ルートに合わせて変更する ↓↓↓
    public function storeOrUpdate(Request $request, User $user, int $year, int $week_number)
    {
        // --- 認可 ---
        // 操作しようとしている目標が、ログインユーザーのものでなければ403エラー
        if ($user->id !== Auth::id()) {
            abort(403, 'アクセス権がありません。');
        }

        WeeklyGoal::updateOrCreate(
            [
                // --- 検索条件 ---
                'user_id' => $user->id, // hidden inputの代わりに、URLの$userを使う
                'year' => $year,
                'week_number' => $week_number,
            ],
            [
                // --- 保存/更新するデータ ---
                'goal_this_week' => $request->input('goal_this_week'),
                'plan_next_week' => $request->input('plan_next_week'),
            ]
        );

        // 保存が終わったら、元の週報表示画面に戻る
        return redirect()->route('weekly-reports.show', [
            'user' => $user, // $userIdの代わりに$userオブジェクトを渡す
            'year' => $year,
            'week_number' => $week_number,
        ])->with('success', '週の目標を保存しました！');
    }
}
