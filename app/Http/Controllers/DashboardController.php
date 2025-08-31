<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\DailyReport;
use App\Models\WeeklyGoal;
use App\Models\User;
use App\Models\Knowledge;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();

        // === 個人データ ===
        // 1. 「今日やるべきこと」を取得 (昨日の日報の「明日やること」)
        $todaysPlan = DailyReport::where('user_id', $user->id)
            ->where('report_date', $yesterday->format('Y-m-d'))
            ->value('summary_tomorrow');

        // 2. 「今日の日報」の提出状況を取得
        $todaysReport = DailyReport::where('user_id', $user->id)
            ->where('report_date', $today->format('Y-m-d'))
            ->first();

        // 3. 「今週の目標」を取得
        $thisWeeksGoal = WeeklyGoal::where('user_id', $user->id)
            ->where('year', $today->year)
            ->where('week_number', $today->weekOfYear)
            ->first();


        // === チームデータ ===

        // 1. 最新の共有事項を取得
        $latestKnowledges = Knowledge::with('user')
            ->published()
            ->orderBy('is_pinned', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(6) // ダッシュボードには最新6件を表示
            ->get();


        // 2. 同じ部署のメンバーの日報提出状況を取得
        $teamMembers = collect(); // 空のコレクションで初期化
        if ($user->division_id) {
            $teamMembers = User::where('division_id', $user->division_id)
                ->where('id', '!=', $user->id) // 自分以外
                ->with(['dailyReports' => function ($query) use ($today) {
                    $query->where('report_date', $today->format('Y-m-d'));
                }])
                ->get();
        }

        return view('dashboard', [
            'user' => $user,
            'todaysPlan' => $todaysPlan,
            'todaysReportExists' => $todaysReport !== null,
            'thisWeeksGoal' => $thisWeeksGoal,
            'latestKnowledges' => $latestKnowledges,
            'teamMembers' => $teamMembers, // チーム情報を追加
        ]);
    }
}
