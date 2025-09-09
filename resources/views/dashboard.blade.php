{{-- resources/views/dashboard.blade.php --}}
<x-portal-layout :showHero="false">
    <div class="min-h-screen">

        {{-- レスポンシブ対応：PC時のみ2列、それ以外は1列 --}}
        <div class="lg:flex lg:gap-8 p-8">

            {{-- =============================================== --}}
            {{-- 左コラム：「私のコックピット」（PC時のみ表示） --}}
            {{-- =============================================== --}}
            <div class="hidden lg:block lg:w-80 lg:flex-shrink-0">
                <div class="bg-white shadow-lg border border-gray-200 rounded-2xl overflow-hidden sticky top-8">
                    {{-- サイドバーヘッダー --}}
                    <div class="p-6 bg-slate-100 text-slate-800">
                        <h2 class="text-xl font-bold mb-2">🏠 私のコックピット</h2>
                        <p class="text-slate-700 font-medium">
                            {{ Carbon\Carbon::today()->format('Y年m月d日') }}（{{ Carbon\Carbon::today()->isoFormat('ddd') }}曜日）
                        </p>
                    </div>

                    {{-- コックピット内容（スクロール可能） --}}
                    <div class="max-h-[calc(100vh-12rem)] overflow-y-auto sidebar-scroll">
                        <div class="p-6 space-y-8">

                            {{-- 今日のToDo --}}
                            <div>
                                <label
                                    class="block text-lg font-bold text-slate-800 mb-3 pb-2 border-b-2 border-gray-300">
                                    ✅ 今日やること
                                </label>
                                <div
                                    class="bg-slate-100 p-4 rounded-xl border border-gray-300 text-sm text-slate-700 min-h-[80px]">
                                    @if ($todaysPlan)
                                        {!! nl2br(e($todaysPlan)) !!}
                                    @else
                                        <span class="text-gray-500 italic">昨日の日報から自動取得します</span>
                                    @endif
                                </div>
                            </div>

                            {{-- 今日の進捗状況 --}}
                            <div>
                                <label
                                    class="block text-lg font-bold text-slate-800 mb-3 pb-2 border-b-2 border-gray-300">
                                    📊 今日の進捗
                                </label>
                                <div class="bg-slate-100 p-4 rounded-xl border border-gray-300">
                                    @if ($todaysReportExists)
                                        <div class="flex items-center text-green-700">
                                            <span class="text-2xl mr-3">✅</span>
                                            <div>
                                                <div class="font-bold">日報提出済み</div>
                                                <div class="text-sm text-gray-600">今日もお疲れ様でした！</div>
                                            </div>
                                        </div>
                                    @else
                                        <div class="flex items-center text-amber-700">
                                            <span class="text-2xl mr-3">📝</span>
                                            <div>
                                                <div class="font-bold">日報未提出</div>
                                                <div class="text-sm text-gray-600">今日の振り返りをしましょう</div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- 今週の目標 --}}
                            <div>
                                <label
                                    class="block text-lg font-bold text-slate-800 mb-3 pb-2 border-b-2 border-gray-300">
                                    🎯 今週の羅針盤
                                </label>
                                <div
                                    class="bg-slate-100 p-4 rounded-xl border border-gray-300 text-sm text-slate-700 min-h-[60px]">
                                    @if ($thisWeeksGoal && $thisWeeksGoal->goal_this_week)
                                        {!! nl2br(e($thisWeeksGoal->goal_this_week)) !!}
                                    @else
                                        <span class="text-gray-500 italic">今週の目標を設定しましょう</span>
                                    @endif
                                </div>
                            </div>

                            {{-- クイックアクション --}}
                            <div>
                                <label
                                    class="block text-lg font-bold text-slate-800 mb-3 pb-2 border-b-2 border-gray-300">
                                    ⚡ クイックアクション
                                </label>
                                <div class="space-y-3">

                                    {{-- 週報を見る --}}
                                    <a href="{{ route('weekly-reports.show', ['user' => Auth::user(), 'year' => Carbon\Carbon::today()->year, 'week_number' => Carbon\Carbon::today()->weekOfYear]) }}"
                                        class="block w-full px-4 py-3 text-center text-sm font-bold bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-all duration-200 shadow-md hover:shadow-lg">
                                        📊 自分の週報を見る
                                    </a>

                                    {{-- 日報関連 --}}
                                    <a href="{{ route('daily-reports.edit', ['user' => Auth::user(), 'date' => Carbon\Carbon::today()->format('Y-m-d')]) }}"
                                        class="block w-full px-4 py-3 text-center text-sm font-bold {{ $todaysReportExists ? 'bg-blue-600 hover:bg-blue-700 text-white' : 'bg-red-500 hover:bg-red-600 text-white' }} rounded-lg transition-all duration-200 shadow-sm hover:shadow-md">
                                        {{ $todaysReportExists ? '✏️ 今日の日報を編集' : '📝 今日の日報を作成' }}
                                    </a>

                                    {{-- 外部リンク --}}
                                    <div class="grid grid-cols-2 gap-2">
                                        <a href="https://zoom.us/j/8691403369?omn=96848537844#success"
                                            class="flex flex-col items-center p-3 bg-white hover:bg-blue-50 border border-gray-300 hover:border-blue-400 rounded-lg transition-all duration-200 text-gray-800 text-xs font-bold"
                                            target="_blank" rel="noopener noreferrer">
                                            <span class="text-lg mb-1">📹</span>
                                            <span>Zoom</span>
                                        </a>
                                        <a href="https://github.com/Mechatron-3rd"
                                            class="flex flex-col items-center p-3 bg-white hover:bg-blue-50 border border-gray-300 hover:border-blue-400 rounded-lg transition-all duration-200 text-gray-800 text-xs font-bold"
                                            target="_blank" rel="noopener noreferrer">
                                            <span class="text-lg mb-1">🐙</span>
                                            <span>GitHub</span>
                                        </a>
                                    </div>
                                </div>
                            </div>

                            {{-- スクロール用の下部余白 --}}
                            <div class="h-8"></div>

                        </div>
                    </div>
                </div>
            </div>

            {{-- =============================================== --}}
            {{-- 右コラム/メインコラム：「チームのインフォメーションボード」 --}}
            {{-- =============================================== --}}
            <div class="flex-1">
                <div class="space-y-8">

                    {{-- ページヘッダー --}}
                    <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6 lg:p-8">
                        <h1 class="text-3xl lg:text-4xl font-bold text-slate-800 mb-2">
                            🌟 Team Dashboard
                        </h1>
                        <p class="text-base lg:text-lg text-gray-600">
                            チーム全体の「今」を一目で把握できる情報ボードです
                        </p>
                    </div>

                    {{-- 共有事項（ナレッジハブ） --}}
                    <div class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden">
                        <div
                            class="bg-slate-100 border-b border-gray-200 p-4 lg:p-6 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
                            <h2 class="text-xl lg:text-2xl font-bold text-slate-800 flex items-center">
                                <span class="text-green-600 mr-3">📢</span>共有事項・お知らせ
                            </h2>
                            <div class="flex gap-2">
                                <button onclick="location.href='{{ route('knowledges.index') }}'"
                                    class="px-4 lg:px-6 py-2 lg:py-3 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-xl font-bold text-xs lg:text-sm transition-all duration-200 shadow-md hover:shadow-lg">
                                    一覧へ
                                </button>
                                <button onclick="location.href='{{ route('knowledges.create') }}'"
                                    class="px-4 lg:px-6 py-2 lg:py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold text-xs lg:text-sm transition-all duration-200 shadow-md hover:shadow-lg">
                                    ➕ 新規投稿
                                </button>
                            </div>
                        </div>

                        <div class="p-4 lg:p-6">
                            @if ($latestKnowledges->isEmpty())
                                {{-- 投稿がない場合の表示 --}}
                                <div class="text-center py-8 lg:py-12 text-gray-500">
                                    <span class="text-4xl lg:text-6xl block mb-4">📝</span>
                                    <p class="text-base lg:text-lg font-medium">まだ投稿がありません</p>
                                    <p class="text-sm mt-2">チームの情報を共有してみましょう！</p>
                                </div>
                            @else
                                {{-- カードグリッド --}}
                                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 lg:gap-6">
                                    @foreach ($latestKnowledges as $knowledge)
                                        <a href="{{ route('knowledges.show', $knowledge) }}"
                                            class="block bg-white rounded-xl border border-gray-200 hover:border-blue-400 hover:shadow-lg transition-all duration-200 transform hover:-translate-y-1 p-4 lg:p-6">

                                            {{-- カードヘッダー --}}
                                            <div class="flex items-start justify-between mb-3">
                                                <span class="font-bold text-base lg:text-lg text-slate-800 break-words">
                                                    {{ $knowledge->title }}
                                                </span>
                                                @if ($knowledge->is_pinned)
                                                    <span
                                                        class="ml-2 flex-shrink-0 text-xs font-bold bg-red-600 text-white px-2 py-1 rounded-full">📌
                                                        重要</span>
                                                @endif
                                            </div>

                                            {{-- 本文の抜粋 --}}
                                            <p class="text-gray-600 text-sm leading-relaxed">
                                                {{ $knowledge->getExcerpt(80) }}
                                            </p>

                                            {{-- カードフッター（投稿者情報） --}}
                                            <div class="mt-4 pt-4 border-t border-gray-200 text-right">
                                                <span class="text-xs text-gray-500">
                                                    👤 {{ $knowledge->user->name }} @
                                                    {{ $knowledge->created_at->format('m/d') }}
                                                </span>
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- チームカレンダー（スケジュールハブ） --}}
                    <div class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden">
                        <div
                            class="bg-slate-100 border-b border-gray-200 p-4 lg:p-6 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
                            <h2 class="text-xl lg:text-2xl font-bold text-slate-800 flex items-center">
                                <span class="text-blue-600 mr-3">📅</span>今週の予定
                            </h2>
                            <a href="{{ route('events.index') }}"
                                class="px-4 lg:px-6 py-2 lg:py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold text-xs lg:text-sm transition-all duration-200 shadow-md hover:shadow-lg">
                                カレンダー全体を見る
                            </a>
                        </div>

                        <div class="p-4 lg:p-6">
                            @if ($thisWeeksEvents->isEmpty())
                                <div class="text-center py-8 lg:py-12 text-gray-500">
                                    <span class="text-4xl lg:text-6xl block mb-4">🎉</span>
                                    <p class="text-base lg:text-lg font-medium">今週はまだ予定がありません</p>
                                </div>
                            @else
                                {{-- レスポンシブ対応グリッドレイアウト --}}
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6">
                                    @foreach ($thisWeeksEvents->groupBy(function ($event) {
        return $event->start_datetime->format('Y-m-d');
    }) as $date => $eventsOnDate)
                                        {{-- 日付ごとのカラム --}}
                                        <div class="flex flex-col">
                                            {{-- 日付ヘッダー --}}
                                            <div class="mb-2">
                                                <span
                                                    class="font-bold text-base lg:text-lg text-slate-700 bg-gray-100 px-3 py-1 rounded-lg">
                                                    {{ \Carbon\Carbon::parse($date)->isoFormat('M月D日 (ddd)') }}
                                                </span>
                                            </div>

                                            {{-- その日の予定をループ --}}
                                            <div class="flex flex-col gap-2">
                                                @foreach ($eventsOnDate as $event)
                                                    <a href="{{ route('events.show', $event) }}"
                                                        class="block p-3 rounded-lg hover:bg-slate-100 border-l-4"
                                                        style="border-color: {{ $event->color }};">
                                                        <p class="font-bold text-sm text-slate-800">{{ $event->title }}
                                                        </p>
                                                        <div
                                                            class="text-xs text-gray-500 flex justify-between items-center mt-1">
                                                            <span>
                                                                @if ($event->is_all_day)
                                                                    終日
                                                                @else
                                                                    {{ $event->start_datetime->format('H:i') }}
                                                                @endif
                                                            </span>
                                                            <span>by {{ $event->user->name }}</span>
                                                        </div>
                                                    </a>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- 最近のプロジェクト --}}
                    <div class="bg-white rounded-lg shadow-md border border-gray-200">
                        <div class="p-4 border-b">
                            <h3 class="text-lg font-bold">📊 最新のプロジェクト</h3>
                        </div>
                        <div class="p-4">
                            <ul class="space-y-4">
                                @forelse ($recentProjects as $project)
                                    <li>
                                        <a href="{{ route('tasks.index', $project) }}"
                                            class="block p-3 bg-gray-50 hover:bg-blue-50 rounded-lg transition">
                                            <div class="flex justify-between items-center">
                                                <span class="font-bold text-blue-700">{{ $project->name }}</span>
                                                <span class="text-sm text-gray-500 bg-white px-2 py-1 rounded-full">
                                                    タスク: {{ $project->tasks_count }}
                                                </span>
                                            </div>
                                        </a>
                                    </li>
                                @empty
                                    <li class="text-gray-500">進行中のプロジェクトはありません。</li>
                                @endforelse
                            </ul>
                            <div class="mt-4 text-right">
                                <a href="{{ route('projects.index') }}"
                                    class="text-sm font-semibold text-blue-600 hover:underline">
                                    全てのプロジェクトを見る →
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- チームの活動状況（デイリーパルス） --}}
                    <div class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden">
                        <div class="bg-slate-100 border-b border-gray-200 p-4 lg:p-6">
                            <h2 class="text-xl lg:text-2xl font-bold text-slate-800 flex items-center">
                                <span class="text-amber-600 mr-3">👥</span>チームの活動状況
                            </h2>
                            <p class="text-sm text-gray-600 mt-2">同じ部署のメンバーの今日の活動状況</p>
                        </div>

                        <div class="divide-y divide-gray-100">
                            @forelse ($teamMembers as $member)
                                <div class="p-4 flex justify-between items-center">
                                    {{-- メンバー名 --}}
                                    <span class="font-bold text-gray-800">{{ $member->name }}</span>

                                    {{-- 日報ステータス --}}
                                    @if (isset($dailyReportStatuses[$member->id]))
                                        {{-- [提出済み] --}}
                                        <span
                                            class="px-3 py-1 bg-green-100 text-green-800 text-xs font-bold rounded-full">
                                            ✅ 提出済み
                                        </span>
                                    @else
                                        {{-- [未提出] --}}
                                        <span
                                            class="px-3 py-1 bg-orange-100 text-orange-800 text-xs font-bold rounded-full">
                                            📝 未提出
                                        </span>
                                    @endif
                                </div>
                            @empty
                                <div class="p-8 text-center text-gray-500">
                                    <p>同じ部署に、他のメンバーがいません。</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            /* サイドバー内のスクロールバーを完全に非表示 */
            .sidebar-scroll::-webkit-scrollbar {
                display: none !important;
            }

            .sidebar-scroll {
                -ms-overflow-style: none !important;
                /* IE and Edge */
                scrollbar-width: none !important;
                /* Firefox */
                scroll-behavior: smooth;
                /* スムーズスクロール */
            }
        </style>
    @endpush
</x-portal-layout>
