<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\DailyReport;
use App\Models\WeeklyGoal;
use App\Models\User;
use App\Models\Knowledge;
use App\Models\Event;
use App\Models\Project;
use App\Models\Task;

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

        // 3. 今週の予定を取得

        // 「今週」の始まり（月曜日）と終わり（日曜日）を定義
        $startOfWeek = now()->startOfWeek(Carbon::MONDAY);
        $endOfWeek = now()->endOfWeek(Carbon::SUNDAY);

        // 今週の予定を取得
        $thisWeeksEvents = Event::visibleTo(Auth::id())
            ->whereBetween('start_datetime', [$startOfWeek, $endOfWeek])
            ->orderBy('start_datetime')
            ->get();



        //チームの日報提出状況
        // 1. ログインユーザーが所属する部署の、他のメンバーを取得
        $teamMembers = User::where('division_id', $user->division_id)
            ->where('id', '!=', $user->id) // 自分自身は除外する
            ->orderBy('name')
            ->get();

        // 2. チームメンバーの「今日の日報の提出状況」をチェックする
        //    pluck()とkeyBy()を使い、メンバーIDをキーにした日報の有無の連想配列を作る
        $dailyReportStatuses = DailyReport::whereIn('user_id', $teamMembers->pluck('id'))
            ->where('report_date', $today->format('Y-m-d'))
            ->get()
            ->keyBy('user_id');


        // 最新のプロジェクトを5件取得
        $recentProjects = Project::withCount('tasks')
            ->latest()
            ->take(5)
            ->get();

        // ログインユーザーが担当する、未完了のタスクを5件取得
        $myTasks = Task::where('user_id', $user->id)
            ->where('status', '!=', 'done')
            ->orderBy('planned_end_date', 'asc')
            ->with('project')
            ->take(5)
            ->get();


        return view('dashboard', [
            'user' => $user,
            'todaysPlan' => $todaysPlan,
            'todaysReportExists' => $todaysReport !== null,
            'thisWeeksGoal' => $thisWeeksGoal,
            'latestKnowledges' => $latestKnowledges,
            'teamMembers' => $teamMembers, // チーム情報を追加
            'thisWeeksEvents' => $thisWeeksEvents, // 今週の予定を追加
            'teamMembers' => $teamMembers, // チームメンバー情報を追加
            'dailyReportStatuses' => $dailyReportStatuses, // 日報提出状況を追加
            'recentProjects' => $recentProjects, // 最新のプロジェクトを追加
            'myTasks' => $myTasks,
        ]);
    }
}
