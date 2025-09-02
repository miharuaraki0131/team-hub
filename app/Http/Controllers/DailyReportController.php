<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreDailyReportRequest;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\DailyReport;
use App\Models\User;
use App\Models\Division;
use App\Models\NotificationDestination;
use App\Notifications\DailyReportUpdated;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;

class DailyReportController extends Controller
{
    /**
     * 指定された日付の日報の、編集画面を表示する
     *
     * @param string $date
     * @return \Illuminate\View\View
     */
    public function edit(User $user, string $date)
    {
        // 認可処理：URLのユーザーとログインユーザーが一致するか確認
        if ($user->id !== Auth::id()) {
            abort(403, 'アクセス権がありません。');
        }

        $reportDate = Carbon::parse($date);

        // 今日の日報を探す（なければ作る）
        $dailyReport = DailyReport::firstOrCreate(
            // ↓↓↓ DBに保存するときに、初めてformat()を使う ↓↓↓
            ['user_id' => $user->id, 'report_date' => $reportDate->format('Y-m-d')],
            ['summary_today' => '', 'discrepancy' => '', 'summary_tomorrow' => '', 'issues_thoughts' => '']
        );

        // 昨日の「明日やること」を取得
        $yesterdayPlan = '';

        // ↓↓↓ $reportDateがオブジェクトなので、copy()が、正しく使える！ ↓↓↓
        $yesterday = $reportDate->copy()->subDay()->format('Y-m-d');

        $yesterdayReport = DailyReport::where('user_id', Auth::id())
            ->where('report_date', $yesterday)
            ->first();

        if ($yesterdayReport) {
            $yesterdayPlan = $yesterdayReport->summary_tomorrow;
        }

        return view('daily-reports.edit', compact('user', 'dailyReport', 'yesterdayPlan'));
    }

    /**
     * 日報を、保存または更新する
     *
     * @param \App\Http\Requests\StoreDailyReportRequest $request
     * @param \App\Models\User $user
     * @param string $date
     * @return \Illuminate\Http\RedirectResponse
     */
    // ↓↓↓ メソッドの引数を、routes/web.phpの定義と一致させる ↓↓↓
    public function storeOrUpdate(StoreDailyReportRequest $request, User $user, string $date)
    {
        // --- 1. 認可 ---
        if ($user->id !== Auth::id()) {
            abort(403, 'アクセス権がありません。');
        }

        // --- 2. バリデーション済みのデータを取得 ---
        $validated = $request->validated();

        // --- 3. データの保存・更新 ---
        $dailyReport = DailyReport::updateOrCreate(
            [
                'user_id' => $user->id,
                'report_date' => $date,
            ],
            [
                'summary_today' => $validated['summary_today'],
                'discrepancy' => $validated['discrepancy'],
                'summary_tomorrow' => $validated['summary_tomorrow'],
                'issues_thoughts' => $validated['issues_thoughts'],
            ]
        );

        // --- 4. 通知処理（最も安全な方法） ---
        try {
            // userが部署に所属している場合のみ通知処理を実行
            if ($user->division_id) {
                $division = Division::with('notificationDestinations')
                    ->find($user->division_id);

                if ($division && $division->notificationDestinations->isNotEmpty()) {
                    $emails = $division->notificationDestinations->pluck('email')->all();

                    Notification::route('mail', $emails)
                        ->notify(new DailyReportUpdated($dailyReport));
                }
            }
        } catch (\Exception $e) {
            // ログにエラーを記録するが、ユーザーには影響しないようにする
            \Log::warning('日報通知の送信に失敗しました: ' . $e->getMessage());
        }

        // リダイレクトは必ず実行
        $reportDate = Carbon::parse($date);
        return redirect()->route('weekly-reports.show', [
            'user' => $user,
            'year' => $reportDate->year,
            'week_number' => $reportDate->weekOfYear,
        ])->with('success', '日報を保存しました！');
    }
}
