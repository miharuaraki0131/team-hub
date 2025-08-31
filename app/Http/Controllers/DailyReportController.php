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
        //    フォームの隠しデータではなく、URLの{user}を正としてチェックする
        if ($user->id !== Auth::id()) {
            abort(403, 'アクセス権がありません。');
        }

        // --- 2. バリデーション済みのデータを取得 ---
        $validated = $request->validated();

        // --- 3. データの保存・更新 ---
        $dailyReport = DailyReport::updateOrCreate(
            [
                // 検索条件には、URLから受け取った、信頼できるパラメータを使う
                'user_id' => $user->id,
                'report_date' => $date, // $validated['report_date'] よりもURLの$dateが正
            ],
            [
                // 更新/作成するデータは、バリデーション済みのものを使う
                'summary_today' => $validated['summary_today'],
                'discrepancy' => $validated['discrepancy'],
                'summary_tomorrow' => $validated['summary_tomorrow'],
                'issues_thoughts' => $validated['issues_thoughts'],
            ]
        );

        // 4. 部署を取得し、その部署に紐づく全ての通知先を「Eager Loading」で一緒に取得する
        $division = $dailyReport->user->division()->with('notificationDestinations')->first();

        // 5. 通知先が存在するかチェック
        if ($division && $division->notificationDestinations->isNotEmpty()) {

            // 6. 全ての通知先メールアドレスを、配列として抽出する
            $emails = $division->notificationDestinations->pluck('email')->all();

            // 7. Notificationファサードで、複数のアドレスに一斉に通知を送る！
            Notification::route('mail', $emails)
                ->notify(new DailyReportUpdated($dailyReport));
        }

        // --- 8. リダイレクト ---
        $reportDate = Carbon::parse($date);
        return redirect()->route('weekly-reports.show', [
            'user' => $user, // ここでもURLから受け取った$userオブジェクトをそのまま使える
            'year' => $reportDate->year,
            'week_number' => $reportDate->weekOfYear,
        ])->with('success', '日報を保存しました！');
    }
}
