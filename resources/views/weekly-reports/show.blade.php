{{-- resources/views/weekly-reports/show.blade.php --}}
<x-portal-layout>
    <div class="flex min-h-screen bg-gray-50">

        {{-- =============================================== --}}
        {{-- サイドバー：ナビゲーションエリア（固定スクロール） --}}
        {{-- =============================================== --}}
        <div
            class="w-80 bg-white shadow-lg border-r border-gray-200 flex flex-col fixed overflow-y-auto mt-8 rounded-r-2xl">
            {{-- サイドバーヘッダー --}}
            <div class="p-6 bg-slate-100 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-800 mb-4">📊 週報ナビゲーション</h2>
            </div>

            {{-- ナビゲーションコンテンツ --}}
            <div class="flex-1 p-6 space-y-8">
                {{-- 部署で絞り込み --}}
                <div>
                    <label class="block text-lg font-bold text-gray-800 mb-3 pb-2 border-b-2 border-blue-400">
                        🏢 部署で絞り込み
                    </label>
                    <select id="division-selector"
                        class="w-full px-4 py-3 text-base border-2 border-gray-300 rounded-lg focus:border-blue-400 focus:ring-2 focus:ring-blue-200 bg-white text-gray-800 font-medium">
                        <option value="">すべての部署</option>
                        @foreach ($divisions as $division)
                            <option value="{{ $division->id }}"
                                {{ $user->division_id == $division->id ? 'selected' : '' }}>
                                {{ $division->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- メンバー選択 --}}
                <div>
                    <label class="block text-lg font-bold text-gray-800 mb-3 pb-2 border-b-2 border-green-400">
                        👤 メンバー選択
                    </label>
                    <select id="user-selector"
                        class="w-full px-4 py-3 text-base border-2 border-gray-300 rounded-lg focus:border-blue-400 focus:ring-2 focus:ring-blue-200 bg-white text-gray-800 font-medium">
                        @foreach ($users as $member)
                            <option value="{{ $member->id }}" data-division-id="{{ $member->division_id }}"
                                {{ $user->id == $member->id ? 'selected' : '' }}>
                                {{ $member->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- 週のナビゲーション --}}
                <div>
                    <label class="block text-lg font-bold text-gray-800 mb-3 pb-2 border-b-2 border-purple-400">
                        📅 週の切り替え
                    </label>
                    <div class="bg-gray-50 p-4 rounded-xl border-2 border-gray-300">
                        @php
                            // 前の週の年と週番号を計算
                            $prevWeek = \Carbon\Carbon::createFromDate($year, 1, 1)
                                ->setISODate($year, $week_number)
                                ->subWeek();
                            // 次の週の年と週番号を計算
                            $nextWeek = \Carbon\Carbon::createFromDate($year, 1, 1)
                                ->setISODate($year, $week_number)
                                ->addWeek();
                        @endphp

                        {{-- 現在の週表示 --}}
                        <div class="text-center mb-4">
                            <div class="text-sm font-medium text-gray-600 mb-1">現在表示中の週</div>
                            <div
                                class="text-lg font-bold text-gray-800 bg-white px-4 py-2 rounded-lg border border-gray-300">
                                {{ \Carbon\Carbon::parse($startOfWeek)->format('Y/m/d') }} ～
                                {{ \Carbon\Carbon::parse($endOfWeek)->format('Y/m/d') }}
                            </div>
                        </div>

                        {{-- 前週・次週ボタン --}}
                        <div class="flex gap-2">
                            <a href="{{ route('weekly-reports.show', ['user' => $user, 'year' => $prevWeek->year, 'week_number' => $prevWeek->weekOfYear]) }}"
                                class="flex-1 px-4 py-3 text-center text-sm font-bold bg-white hover:bg-blue-100 border-2 border-gray-300 hover:border-blue-400 rounded-lg transition-all duration-200 text-gray-800">
                                ← 前の週
                            </a>
                            <a href="{{ route('weekly-reports.show', ['user' => $user, 'year' => $nextWeek->year, 'week_number' => $nextWeek->weekOfYear]) }}"
                                class="flex-1 px-4 py-3 text-center text-sm font-bold bg-white hover:bg-blue-100 border-2 border-gray-300 hover:border-blue-400 rounded-lg transition-all duration-200 text-gray-800">
                                次の週 →
                            </a>
                        </div>
                    </div>
                </div>

                {{-- クイックアクション --}}
                <div>
                    <label class="block text-lg font-bold text-gray-800 mb-3 pb-2 border-b-2 border-orange-400">
                        ⚡ クイックアクション
                    </label>
                    <div class="space-y-3">
                        <a href="{{ route('weekly-reports.show', ['user' => Auth::user(), 'year' => now()->year, 'week_number' => now()->weekOfYear]) }}"
                            class="block w-full px-4 py-3 text-center text-sm font-bold bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition-all duration-200 shadow-md hover:shadow-lg">
                            🏠 自分の今週の週報
                        </a>
                        {{-- 日報関連 --}}
                        <a href="{{ route('daily-reports.edit', ['user' => $user, 'date' => Carbon\Carbon::today()->format('Y-m-d')]) }}"
                            class="block w-full px-4 py-3 text-center text-sm font-bold {{ $todaysReportExists ? 'bg-blue-500 hover:bg-blue-600' : 'bg-yellow-400 hover:bg-yellow-500' }} {{ $todaysReportExists ? 'text-white' : 'text-gray-800' }} rounded-lg transition-all duration-200 shadow-md hover:shadow-lg">
                            {{ $todaysReportExists ? '✏️ 今日の日報を編集' : '📝 今日の日報を作成' }}
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- =============================================== --}}
        {{-- メインコンテンツエリア（カードで囲む） --}}
        {{-- =============================================== --}}
        <div class="flex-1 ml-80 overflow-auto p-8">
            <div class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden">
                {{-- メインヘッダー --}}
                <div class="bg-slate-100 border-b-2 border-gray-200 p-8">
                    <h1 class="text-4xl font-bold text-gray-800">
                        📊 {{ $user->name }} の週間報告
                    </h1>
                    <p class="text-lg text-gray-600 mt-2">
                        {{ \Carbon\Carbon::parse($startOfWeek)->format('Y年m月d日') }}（月）〜
                        {{ \Carbon\Carbon::parse($endOfWeek)->format('m月d日') }}（金）
                    </p>
                </div>

                <div class="p-8">
                    {{-- フラッシュメッセージ --}}
                    <x-flash-message />

                    {{-- =============================================== --}}
                    {{-- 週の目標と予定 --}}
                    {{-- =============================================== --}}
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-12">
                        {{-- 今週の目標・総括 --}}
                        <div class="bg-blue-50 p-8 rounded-2xl border-2 border-slate-300 shadow-md">
                            <div class="flex justify-between items-center mb-6">
                                <h2 class="text-2xl font-bold text-gray-800">🎯 今週の目標・総括</h2>
                                @if (Auth::id() === $user->id)
                                    <a href="{{ route('weekly-goals.edit', ['user' => $user, 'year' => $year, 'week_number' => $week_number]) }}"
                                        class="px-6 py-3 text-lg font-bold bg-white hover:bg-blue-100 border-2 border-slate-400 hover:border-blue-400 rounded-xl transition-all duration-200 text-gray-800 shadow-sm hover:shadow-md">
                                        ✏️ 編集
                                    </a>
                                @endif
                            </div>
                            <div
                                class="text-lg leading-relaxed text-gray-800 bg-white p-6 rounded-xl border-2 border-slate-300 font-medium min-h-32">
                                {!! nl2br(e($weeklyGoal->goal_this_week ?? '（未設定）')) !!}
                            </div>
                        </div>
                        {{-- 来週の予定 --}}
                        <div class="bg-green-50 p-8 rounded-2xl border-2 border-slate-300 shadow-md">
                            <h2 class="text-2xl font-bold text-gray-800 mb-6">📅 来週の予定</h2>
                            <div
                                class="text-lg leading-relaxed text-gray-800 bg-white p-6 rounded-xl border-2 border-slate-300 font-medium min-h-32">
                                {!! nl2br(e($weeklyGoal->plan_next_week ?? '（未設定）')) !!}
                            </div>
                        </div>
                    </div>

                    {{-- =============================================== --}}
                    {{-- 日報リスト（月〜金） --}}
                    {{-- =============================================== --}}
                    <div class="space-y-6">
                        <h2 class="text-3xl font-bold text-gray-800 pb-3 border-b-3 border-blue-400 mb-8">📋 日報一覧</h2>

                        @php
                            // コントローラーから渡された、月曜日のCarbonオブジェクトをコピー
                            $currentDay = $startOfWeek->copy();
                        @endphp

                        {{-- $currentDayが、$endOfWeek（金曜日）になるまで、ループを続ける --}}
                        @while ($currentDay->lte($endOfWeek))
                            @php
                                // その日の日付キー（Y-m-d）を作成
                                $dateKey = $currentDay->format('Y-m-d');
                                // その日の日報データを、$dailyReportsコレクションから、キーを使って一撃で取得
                                $report = $dailyReports[$dateKey] ?? null;

                                // 今日の日付と比較
                                $isToday = $currentDay->format('Y-m-d') === \Carbon\Carbon::today()->format('Y-m-d');

                                // 今日なら色付き、それ以外はグレー
                                if ($isToday) {
                                    $colors = [
                                        'bg' => 'bg-yellow-100',
                                        'border' => 'border-yellow-400',
                                    ];
                                } else {
                                    $colors = [
                                        'bg' => 'bg-gray-100',
                                        'border' => 'border-slate-300',
                                    ];
                                }
                            @endphp
                            <div
                                class="border-2 {{ $colors['border'] }} rounded-2xl overflow-hidden shadow-md bg-white">
                                {{-- 日付ヘッダー --}}
                                <div
                                    class="{{ $colors['bg'] }} px-8 py-6 flex justify-between items-center border-b-2 {{ $colors['border'] }}">
                                    <h3 class="text-2xl font-bold text-gray-800">
                                        @if ($isToday)
                                            ⭐ {{ $currentDay->format('m/d') }}
                                            <span
                                                class="text-xl font-bold ml-2 px-3 py-1 bg-yellow-400 text-gray-900 rounded-full">{{ $currentDay->isoFormat('ddd') }}曜日</span>
                                        @else
                                            📅 {{ $currentDay->format('m/d') }}
                                            <span
                                                class="text-xl font-bold ml-2">({{ $currentDay->isoFormat('ddd') }}曜日)</span>
                                        @endif
                                    </h3>
                                    @if (Auth::id() === $user->id)
                                        <a href="{{ route('daily-reports.edit', ['user' => $user, 'date' => $dateKey]) }}"
                                            class="px-8 py-4 text-lg font-bold @if ($isToday) bg-yellow-300 hover:bg-yellow-400 border-2 border-yellow-500 hover:border-yellow-600 @else bg-white hover:bg-blue-100 border-2 border-slate-400 hover:border-blue-400 @endif rounded-xl transition-all duration-200 text-gray-800 shadow-md hover:shadow-lg transform hover:-translate-y-1">
                                            {{ $report ? '✏️ 編集' : '➕ 作成' }}
                                        </a>
                                    @endif
                                </div>
                                {{-- 日報の内容 --}}
                                @if ($report)
                                    <div class="p-8 grid grid-cols-1 xl:grid-cols-4 gap-8">
                                        <div class="bg-white p-6 rounded-xl border-2 border-slate-300 shadow-sm">
                                            <h4
                                                class="text-lg font-bold mb-4 text-gray-800 pb-2 border-b-2 border-green-400">
                                                ✅ 今日やったこと</h4>
                                            <div class="text-base leading-relaxed text-gray-800 font-medium">
                                                {!! nl2br(e($report->summary_today)) !!}</div>
                                        </div>
                                        <div class="bg-white p-6 rounded-xl border-2 border-slate-300 shadow-sm">
                                            <h4
                                                class="text-lg font-bold mb-4 text-gray-800 pb-2 border-b-2 border-orange-400">
                                                📊 目標との差異</h4>
                                            <div class="text-base leading-relaxed text-gray-800 font-medium">
                                                {!! nl2br(e($report->discrepancy)) !!}</div>
                                        </div>
                                        <div class="bg-white p-6 rounded-xl border-2 border-slate-300 shadow-sm">
                                            <h4
                                                class="text-lg font-bold mb-4 text-gray-800 pb-2 border-b-2 border-blue-400">
                                                📝 明日やること</h4>
                                            <div class="text-base leading-relaxed text-gray-800 font-medium">
                                                {!! nl2br(e($report->summary_tomorrow)) !!}</div>
                                        </div>
                                        <div class="bg-white p-6 rounded-xl border-2 border-slate-300 shadow-sm">
                                            <h4
                                                class="text-lg font-bold mb-4 text-gray-800 pb-2 border-b-2 border-purple-400">
                                                💭 困っていることや感想</h4>
                                            <div class="text-base leading-relaxed text-gray-800 font-medium">
                                                {!! nl2br(e($report->issues_thoughts)) !!}</div>
                                        </div>
                                    </div>
                                @else
                                    <div class="p-12 text-center">
                                        <p class="text-xl text-gray-600 font-medium">📝 日報はまだ作成されていません</p>
                                        @if (Auth::id() === $user->id)
                                            <p class="text-lg text-gray-500 mt-2">上の「➕ 作成」ボタンから日報を作成してください</p>
                                        @endif
                                    </div>
                                @endif
                            </div>

                            @php
                                // 次の日に進める
                                $currentDay->addDay();
                            @endphp
                        @endwhile
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const divisionSelector = document.getElementById('division-selector');
            const userSelector = document.getElementById('user-selector');

            if (divisionSelector && userSelector) {
                // 全ユーザーオプションを保存
                const allUserOptions = Array.from(userSelector.options).slice();

                // ページ読み込み時のフィルタリング
                filterUsersByDivision(divisionSelector.value);

                // 部署変更時の処理
                divisionSelector.addEventListener('change', function() {
                    filterUsersByDivision(this.value);
                });

                // ユーザー変更時の処理
                userSelector.addEventListener('change', function() {
                    if (this.value !== '') {
                        redirectToUser(this.value);
                    }
                });

                // フィルタリング関数
                function filterUsersByDivision(selectedDivisionId) {
                    userSelector.innerHTML = '';

                    // 最初に「-選択してください-」オプションを追加
                    const defaultOption = document.createElement('option');
                    defaultOption.value = '';
                    defaultOption.textContent = '- 選択してください -';
                    defaultOption.selected = true;
                    userSelector.appendChild(defaultOption);

                    allUserOptions.forEach(option => {
                        const shouldShow = selectedDivisionId === '' || option.dataset.divisionId ===
                            selectedDivisionId;

                        if (shouldShow) {
                            const newOption = option.cloneNode(true);
                            newOption.selected = false;
                            userSelector.appendChild(newOption);
                        }
                    });
                }

                // ページ遷移関数
                function redirectToUser(userId) {
                    const year = {{ $year }};
                    const week_number = {{ $week_number }};
                    const url = `/weekly-reports/${userId}/${year}/${week_number}`;
                    window.location.href = url;
                }
            }
        });
    </script>
</x-portal-layout>
