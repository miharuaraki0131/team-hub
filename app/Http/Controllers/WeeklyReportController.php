<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\WeeklyGoal;
use App\Models\DailyReport;
use App\Models\User;
use App\Models\Division;


class WeeklyReportController extends Controller
{
    /**
     * 指定された「ユーザー」の、指定された週の週報を表示する
     *
     * @param \App\Models\User $user
     * @param int $year
     * @param int $week_number
     * @return \Illuminate\View\View
     */
    // ↓↓↓ メソッドの引数を、このように、変更する ↓↓↓
    public function show(User $user, int $year, int $week_number)
    {
        // --- 1. 時間の基準点を設定 ---
        $date = Carbon::now();
        $date->setISODate($year, $week_number); // 指定された年と週の月曜日
        $today = Carbon::today();

        // --- 2. 週の始まりと終わりを計算 ---
        $startOfWeek = $date->copy()->startOfWeek(Carbon::MONDAY);
        $endOfWeek = $date->copy()->endOfWeek(Carbon::FRIDAY);

        // --- 3. データベースから、必要なデータを取得 ---

        // A. 週の目標を取得 (なければ、空のモデルを返す)
        $weeklyGoal = WeeklyGoal::firstOrNew(
            [
                'user_id' => $user->id,
                'year' => $year,
                'week_number' => $week_number,
            ]
        );

        // B. その週の、すべての日報を取得
        $dailyReports = DailyReport::where('user_id', $user->id)
            ->whereBetween('report_date', [$startOfWeek->format('Y-m-d'), $endOfWeek->format('Y-m-d')])
            ->orderBy('report_date', 'asc')
            ->get()
            ->keyBy(function ($item) {
                // コレクションのキーを、'id'ではなく、'report_date' (Y-m-d形式) に変更する
                return Carbon::parse($item->report_date)->format('Y-m-d');
            });

        $divisions = Division::orderBy('name')->get(); // 全ての部署を取得
        $users = User::orderBy('name')->with('division')->get();

        // 2. 「今日の日報」の提出状況を取得
        $todaysReport = DailyReport::where('user_id', $user->id)
            ->where('report_date', $today->format('Y-m-d'))
            ->first();

        // --- 4. ビューに、すべてのデータを渡す ---
        return view('weekly-reports.show', [
            'user' => $user,
            'users' => $users,
            'divisions' => $divisions,
             'todaysReportExists' => $todaysReport !== null,
            'year' => $year,
            'week_number' => $week_number,
            'startOfWeek' => $startOfWeek,
            'endOfWeek' => $endOfWeek,
            'weeklyGoal' => $weeklyGoal,
            'dailyReports' => $dailyReports,
        ]);
    }
}
