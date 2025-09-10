{{-- resources/views/daily-reports/edit.blade.php --}}

<x-portal-layout>
    <div class="bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-200">
        <form method="POST"
            action="{{ route('daily-reports.storeOrUpdate', ['user' => $user, 'date' => $dailyReport->report_date->format('Y-m-d')]) }}"
            class="flex flex-col h-full">
            @csrf
            <input type="hidden" name="report_date" value="{{ $dailyReport->report_date->format('Y-m-d') }}">

            {{-- ヘッダー（見やすく、分かりやすく） --}}
            <div class="p-8 bg-slate-100 border-b-2 border-gray-200">
                <div class="flex flex-col sm:flex-row justify-between items-center">
                    <div>
                        <h1 class="text-4xl font-bold text-gray-800 mb-2">📋 日報</h1>
                        <p class="text-xl text-gray-700 font-medium">
                            {{ $dailyReport->report_date->format('Y年m月d日 (D)') }}
                        </p>
                    </div>
                    <a href="{{ route('weekly-reports.show', ['user' => $user, 'year' => $dailyReport->report_date->year, 'week_number' => $dailyReport->report_date->weekOfYear]) }}"
                        class="mt-4 sm:mt-0 inline-block px-8 py-4 rounded-xl bg-white hover:bg-gray-100 border-2 border-gray-300 hover:border-gray-400 transition-all duration-200 text-lg font-bold text-gray-800 shadow-md hover:shadow-lg">
                        📅 週報に戻る
                    </a>
                </div>
            </div>

            {{-- メインコンテンツ --}}
            <div class="p-8 md:p-12 flex-grow">
                {{-- フラッシュメッセージ --}}
                <x-flash-message />

                {{-- 今日の目標（昨日の予定） --}}
                @if ($yesterdayPlan)
                    <div class="mb-12 bg-yellow-50 border-l-8 border-yellow-400 p-8 rounded-r-2xl shadow-sm">
                        <label class="flex items-center text-2xl font-bold mb-4 text-gray-800">
                            🎯 今日の目標（昨日の予定）
                        </label>
                        <div
                            class="text-lg leading-relaxed text-gray-800 bg-white p-6 rounded-xl border border-yellow-200 font-medium">
                            {!! nl2br(e($yesterdayPlan)) !!}
                        </div>
                    </div>
                @endif

                {{-- フォームセクション --}}
                <div class="grid grid-cols-1 xl:grid-cols-2 gap-x-12 gap-y-12">
                    <x-textarea-card label="✅ 今日やったこと" name="summary_today" :value="$dailyReport->summary_today" />
                    <x-textarea-card label="📊 目標との差異" name="discrepancy" :value="$dailyReport->discrepancy" />
                    <x-textarea-card label="📝 明日やること" name="summary_tomorrow" :value="$dailyReport->summary_tomorrow" />
                    <x-textarea-card label="💭 困っていることや感想" name="issues_thoughts" :value="$dailyReport->issues_thoughts" />
                </div>
            </div>

            {{-- フッター --}}
            <div class="bg-gray-50 px-8 py-8 flex justify-center items-center border-t-2 border-gray-200">
                <button type="submit"
                    class="inline-block px-12 py-5 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-xl font-bold text-white shadow-lg hover:shadow-xl transform hover:-translate-y-1 border-2 border-gray-400">
                    💾 この内容で保存する
                </button>
            </div>
        </form>
    </div>
</x-portal-layout>
