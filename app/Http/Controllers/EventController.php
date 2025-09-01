<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class EventController extends Controller
{
    /**
     * カレンダー表示（メイン画面）
     */
    public function index(Request $request)
    {
        $year = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);

        // 年月の妥当性チェック
        $year = max(2020, min(2030, (int)$year));
        $month = max(1, min(12, (int)$month));

        $currentDate = Carbon::create($year, $month, 1);

        return view('events.index', [
            'currentDate' => $currentDate,
            'year' => $year,
            'month' => $month,
        ]);
    }

    /**
     * FullCalendar用のJSONデータを返すAPI
     */
    public function getEvents(Request $request)
    {
        $start = Carbon::parse($request->get('start'));
        $end = Carbon::parse($request->get('end'));

        $events = Event::with('user')
            ->visibleTo(Auth::id())
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('start_datetime', [$start, $end])
                    ->orWhereBetween('end_datetime', [$start, $end])
                    ->orWhere(function ($qq) use ($start, $end) {
                        $qq->where('start_datetime', '<=', $start)
                            ->where('end_datetime', '>=', $end);
                    });
            })
            ->orderBy('start_datetime')
            ->get();

        return response()->json(
            $events->map(function ($event) {
                return $event->toFullCalendarArray();
            })
        );
    }

    /**
     * イベント作成フォーム
     */
    public function create(Request $request)
    {
        // クエリパラメータから初期値を取得
        $defaultDate = $request->get('date', now()->format('Y-m-d'));
        $defaultTime = $request->get('time', '09:00');

        return view('events.create', [
            'defaultDate' => $defaultDate,
            'defaultTime' => $defaultTime,
        ]);
    }

    /**
     * イベント作成処理
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['nullable', 'string'],
            'start_date' => ['required', 'date'],
            'start_time' => ['required_unless:is_all_day,true', 'date_format:H:i'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'end_time' => ['required_unless:is_all_day,true', 'date_format:H:i'],
            'is_all_day' => ['boolean'],
            'category' => ['nullable', 'string', 'max:100'],
            'visibility' => ['required', 'in:public,private'],
            'color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);

        // 日時の組み立て（FullCalendarの終日イベント処理を考慮）
        // is_all_day が true, 1, "1" のいずれかであるかを判定
        if (($validated['is_all_day'] ?? false) == true) {
            $startDatetime = Carbon::parse($validated['start_date'])->startOfDay();
            // 終日イベントの場合、終了日はstartOfDay()を使用（endOfDay()は使わない）
            $endDatetime = Carbon::parse($validated['end_date'])->startOfDay();
        } else {
            $startDatetime = Carbon::parse($validated['start_date'] . ' ' . ($validated['start_time'] ?? '00:00'));
            $endDatetime = Carbon::parse($validated['end_date'] . ' ' . ($validated['end_time'] ?? '23:59'));
        }

        // 終了時刻が開始時刻より前の場合はエラー
        if ($endDatetime <= $startDatetime) {
            // 終日イベントで同じ日の場合は許可する
            if (!($validated['is_all_day'] && $validated['start_date'] === $validated['end_date'])) {
                return back()->withErrors(['end_time' => '終了時刻は開始時刻より後に設定してください。'])->withInput();
            }
        }

        Event::create([
            'user_id' => Auth::id(),
            'title' => $validated['title'],
            'body' => $validated['body'],
            'start_datetime' => $startDatetime,
            'end_datetime' => $endDatetime,
            'is_all_day' => $validated['is_all_day'] ?? false,
            'category' => $validated['category'],
            'visibility' => $validated['visibility'],
            'color' => $validated['color'],
        ]);

        return redirect()->route('events.index', [
            'year' => $startDatetime->year,
            'month' => $startDatetime->month,
        ])->with('success', '予定を作成しました！');
    }

    /**
     * イベント詳細表示
     */
    public function show(Event $event)
    {
        // 権限チェック
        if ($event->visibility === 'private' && $event->user_id !== Auth::id()) {
            abort(404);
        }

        return view('events.show', compact('event'));
    }

    /**
     * イベント編集フォーム
     */
    public function edit(Event $event)
    {
        // 権限チェック：作成者のみ編集可能
        if ($event->user_id !== Auth::id()) {
            abort(403);
        }

        return view('events.edit', compact('event'));
    }

    /**
     * イベント更新処理
     */
    public function update(Request $request, Event $event)
    {
        // 権限チェック
        if ($event->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['nullable', 'string'],
            'start_date' => ['required', 'date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'end_time' => ['nullable', 'date_format:H:i'],
            'is_all_day' => ['boolean'],
            'category' => ['nullable', 'string', 'max:100'],
            'visibility' => ['required', 'in:public,private'],
            'color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);

        // 日時の組み立て（FullCalendarの終日イベント処理を考慮）
        if ($validated['is_all_day'] ?? false) {
            $startDatetime = Carbon::parse($validated['start_date'])->startOfDay();
            // 終日イベントの場合、終了日はstartOfDay()を使用（endOfDay()は使わない）
            $endDatetime = Carbon::parse($validated['end_date'])->startOfDay();
        } else {
            $startDatetime = Carbon::parse($validated['start_date'] . ' ' . ($validated['start_time'] ?? '00:00'));
            $endDatetime = Carbon::parse($validated['end_date'] . ' ' . ($validated['end_time'] ?? '23:59'));
        }

        if ($endDatetime <= $startDatetime) {
            // 終日イベントで同じ日の場合は許可する
            if (!($validated['is_all_day'] && $validated['start_date'] === $validated['end_date'])) {
                return response()->json(['error' => 'End time must be after start time'], 422);
            }
        }

        $event->update([
            'title' => $validated['title'],
            'body' => $validated['body'],
            'start_datetime' => $startDatetime,
            'end_datetime' => $endDatetime,
            'is_all_day' => $validated['is_all_day'] ?? false,
            'category' => $validated['category'],
            'visibility' => $validated['visibility'],
            'color' => $validated['color'],
        ]);


        Log::info('Received data:', $validated);
        Log::info('Calculated dates:', [
            'start' => $startDatetime,
            'end' => $endDatetime
        ]);

        // Ajax リクエストの場合はJSON、通常のリクエストの場合はリダイレクト
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'event' => $event->toFullCalendarArray()]);
        }

        return redirect()->route('events.show', $event)
            ->with('success', '予定を更新しました！');
    }

    /**
     * イベント削除
     */
    public function destroy(Event $event)
    {
        // 権限チェック
        if ($event->user_id !== Auth::id() && !Auth::user()->is_admin) {
            abort(403);
        }

        $event->delete();

        return redirect()->route('events.index')
            ->with('success', '予定を削除しました。');
    }
}
