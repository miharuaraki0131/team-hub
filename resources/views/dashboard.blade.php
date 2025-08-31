{{-- resources/views/dashboard.blade.php --}}
<x-portal-layout :showHero="false">
    <div class="flex min-h-screen bg-gray-50">

        {{-- =============================================== --}}
        {{-- 左サイドバー：「私のコックピット」（固定幅） --}}
        {{-- =============================================== --}}
        <div
            class="w-80 bg-white shadow-lg border-r border-gray-200 flex flex-col fixed overflow-y-auto mt-8 rounded-r-2xl">
            {{-- サイドバーヘッダー --}}
            <div class="p-6 bg-slate-100 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-800 mb-2">🏠 私のコックピット</h2>
                <p class="text-sm text-gray-600">
                    {{ Carbon\Carbon::today()->format('Y年m月d日') }}（{{ Carbon\Carbon::today()->isoFormat('ddd') }}曜日）</p>
            </div>

            {{-- コックピット内容 --}}
            <div class="flex-1 p-6 space-y-8">

                {{-- 今日のToDo --}}
                <div>
                    <label class="block text-lg font-bold text-gray-800 mb-3 pb-2 border-b-2 border-blue-400">
                        ✅ 今日やること
                    </label>
                    <div class="bg-white p-4 rounded-xl border-2 border-gray-300 text-sm text-gray-800 min-h-[80px]">
                        @if ($todaysPlan)
                            {!! nl2br(e($todaysPlan)) !!}
                        @else
                            <span class="text-gray-500 italic">昨日の日報から自動取得します</span>
                        @endif
                    </div>
                </div>

                {{-- 今日の進捗状況 --}}
                <div>
                    <label class="block text-lg font-bold text-gray-800 mb-3 pb-2 border-b-2 border-green-400">
                        📊 今日の進捗
                    </label>
                    <div class="bg-white p-4 rounded-xl border-2 border-gray-300">
                        @if ($todaysReportExists)
                            <div class="flex items-center text-green-700">
                                <span class="text-2xl mr-3">✅</span>
                                <div>
                                    <div class="font-bold">日報提出済み</div>
                                    <div class="text-sm text-gray-600">今日のお疲れ様でした！</div>
                                </div>
                            </div>
                        @else
                            <div class="flex items-center text-orange-700">
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
                    <label class="block text-lg font-bold text-gray-800 mb-3 pb-2 border-b-2 border-purple-400">
                        🎯 今週の羅針盤
                    </label>
                    <div class="bg-white p-4 rounded-xl border-2 border-gray-300 text-sm text-gray-800 min-h-[60px]">
                        @if ($thisWeeksGoal && $thisWeeksGoal->goal_this_week)
                            {!! nl2br(e($thisWeeksGoal->goal_this_week)) !!}
                        @else
                            <span class="text-gray-500 italic">今週の目標を設定しましょう</span>
                        @endif
                    </div>
                </div>

                {{-- クイックアクション --}}
                <div>
                    <label class="block text-lg font-bold text-gray-800 mb-3 pb-2 border-b-2 border-orange-400">
                        ⚡ クイックアクション
                    </label>
                    <div class="space-y-3">

                        {{-- 週報を見る --}}
                        <a href="{{ route('weekly-reports.show', ['user' => $user, 'year' => Carbon\Carbon::today()->year, 'week_number' => Carbon\Carbon::today()->weekOfYear]) }}"
                            class="block w-full px-4 py-3 text-center text-sm font-bold bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition-all duration-200 shadow-md hover:shadow-lg">
                            📊 自分の週報を見る
                        </a>

                        {{-- 日報関連 --}}
                        <a href="{{ route('daily-reports.edit', ['user' => $user, 'date' => Carbon\Carbon::today()->format('Y-m-d')]) }}"
                            class="block w-full px-4 py-3 text-center text-sm font-bold {{ $todaysReportExists ? 'bg-blue-500 hover:bg-blue-600' : 'bg-yellow-400 hover:bg-yellow-500' }} {{ $todaysReportExists ? 'text-white' : 'text-gray-800' }} rounded-lg transition-all duration-200 shadow-md hover:shadow-lg">
                            {{ $todaysReportExists ? '✏️ 今日の日報を編集' : '📝 今日の日報を作成' }}
                        </a>

                        {{-- 外部リンク --}}
                        <div class="grid grid-cols-2 gap-2">
                            <a href="https://zoom.us/j/8691403369?omn=96848537844#success"
                                class="flex flex-col items-center p-3 bg-white hover:bg-blue-100 border-2 border-gray-300 hover:border-blue-400 rounded-lg transition-all duration-200 text-gray-800 text-xs font-bold"
                                target="_blank" rel="noopener noreferrer">
                                <span class="text-lg mb-1">📹</span>
                                <span>Zoom</span>
                            </a>
                            <a href="https://github.com/Mechatron-3rd"
                                class="flex flex-col items-center p-3 bg-white hover:bg-blue-100 border-2 border-gray-300 hover:border-blue-400 rounded-lg transition-all duration-200 text-gray-800 text-xs font-bold"
                                target="_blank" rel="noopener noreferrer">
                                <span class="text-lg mb-1">🐙</span>
                                <span>GitHub</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- =============================================== --}}
        {{-- 右メインコンテンツ：「チームのインフォメーションボード」 --}}
        {{-- =============================================== --}}
        <div class="flex-1 ml-80 overflow-auto p-8">
            <div class="space-y-8">

                {{-- ページヘッダー --}}
                <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-8">
                    <h1 class="text-4xl font-bold text-gray-800 mb-2">
                        🌟 Team Dashboard
                    </h1>
                    <p class="text-lg text-gray-600">
                        チーム全体の「今」を一目で把握できる情報ボードです
                    </p>
                </div>

                {{-- 共有事項（ナレッジハブ） --}}
                <div class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden">
                    <div class="bg-blue-100 border-b-2 border-gray-200 p-6 flex justify-between items-center">
                        <h2 class="text-2xl font-bold text-gray-800 flex items-center">
                            <span class="mr-3">📢</span>共有事項・お知らせ
                        </h2>
                        <div class="flex gap-2">
                            <button onclick="location.href='{{ route('knowledges.index') }}'"
                                class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-xl font-bold text-sm transition-all duration-200 shadow-md hover:shadow-lg">
                                一覧へ
                            </button>
                            <button onclick="location.href='{{ route('knowledges.create') }}'"
                                class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold text-sm transition-all duration-200 shadow-md hover:shadow-lg">
                                ➕ 新規投稿
                            </button>
                        </div>
                    </div>

                    <div class="p-6">
                        @if ($latestKnowledges->isEmpty())
                            {{-- 投稿がない場合の表示 --}}
                            <div class="text-center py-12 text-gray-500">
                                <span class="text-6xl block mb-4">📝</span>
                                <p class="text-lg font-medium">まだ投稿がありません</p>
                                <p class="text-sm mt-2">チームの情報を共有してみましょう！</p>
                            </div>
                        @else
                            {{-- カードグリッド --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                @foreach ($latestKnowledges as $knowledge)
                                    <a href="{{ route('knowledges.show', $knowledge) }}"
                                        class="block bg-white rounded-xl border-2 border-gray-200 hover:border-blue-400 hover:shadow-lg transition-all duration-200 transform hover:-translate-y-1 p-6">

                                        {{-- カードヘッダー --}}
                                        <div class="flex items-start justify-between mb-3">
                                            <span class="font-bold text-lg text-gray-800 break-words">
                                                {{ $knowledge->title }}
                                            </span>
                                            @if ($knowledge->is_pinned)
                                                <span
                                                    class="ml-2 flex-shrink-0 text-xs font-bold bg-red-500 text-white px-2 py-1 rounded-full">📌
                                                    重要</span>
                                            @endif
                                        </div>

                                        {{-- 本文の抜粋 --}}
                                        <p class="text-gray-600 text-sm leading-relaxed">
                                            {{-- Knowledgeモデルに getExcerpt() メソッドを実装する必要があります --}}
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
                    <div class="bg-green-100 border-b-2 border-gray-200 p-6 flex justify-between items-center">
                        <h2 class="text-2xl font-bold text-gray-800 flex items-center">
                            <span class="mr-3">📅</span>チームカレンダー
                        </h2>
                        <button
                            class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white rounded-xl font-bold text-sm transition-all duration-200 shadow-md hover:shadow-lg">
                            ➕ 予定を追加
                        </button>
                    </div>

                    <div class="p-6">
                        {{-- ここに将来的にFullCalendar.jsのカレンダーが入る --}}
                        <div class="text-center py-12 text-gray-500">
                            <span class="text-6xl block mb-4">📆</span>
                            <p class="text-lg font-medium">カレンダー機能を準備中</p>
                            <p class="text-sm mt-2">FullCalendar.jsを使用した共有カレンダーを実装予定です</p>
                        </div>
                    </div>
                </div>

                {{-- チームの活動状況（デイリーパルス） --}}
                <div class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden">
                    <div class="bg-orange-100 border-b-2 border-gray-200 p-6">
                        <h2 class="text-2xl font-bold text-gray-800 flex items-center">
                            <span class="mr-3">👥</span>チームのデイリーパルス
                        </h2>
                        <p class="text-sm text-gray-600 mt-2">同じ部署のメンバーの今日の活動状況</p>
                    </div>

                    <div class="p-6">
                        {{-- ここに将来的にチームメンバーの日報提出状況が入る --}}
                        <div class="text-center py-12 text-gray-500">
                            <span class="text-6xl block mb-4">💓</span>
                            <p class="text-lg font-medium">チーム活動状況を準備中</p>
                            <p class="text-sm mt-2">部署メンバーの日報提出状況をリアルタイム表示予定です</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-portal-layout>
