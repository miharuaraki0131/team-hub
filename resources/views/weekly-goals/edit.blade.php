{{-- resources/views/weekly-goals/edit.blade.php --}}
<x-portal-layout>
    <div class="bg-white rounded-2xl shadow-lg p-8 md:p-12 border border-gray-200">
        <h1 class="text-4xl font-bold text-gray-800 mb-2">🎯 週の目標を編集</h1>
        <p class="text-xl text-gray-600 font-bold mb-8">
            {{ $startOfWeek->format('Y/m/d') }} ～ {{ $endOfWeek->format('Y/m/d') }}
        </p>

        {{-- 保存・更新処理へのフォーム --}}
        <form
            action="{{ route('weekly-goals.storeOrUpdate', ['user' => $user, 'year' => $year, 'week_number' => $week_number]) }}"
            method="POST"> @csrf {{-- ← CSRF対策で必須！ --}}

            {{-- ルートにパラメータは不要だが、どの週の目標かコントローラーに教えるために必要 --}}
            <input type="hidden" name="year" value="{{ $year }}">
            <input type="hidden" name="week_number" value="{{ $week_number }}">

            <div class="space-y-8">
                {{-- 今週の目標・総括 --}}
                <div>
                    <label for="goal_this_week" class="text-2xl font-bold text-gray-800 mb-4 block">
                        今週の目標・総括
                    </label>
                    <textarea id="goal_this_week" name="goal_this_week" rows="6"
                        class="w-full text-lg p-4 border-2 border-gray-300 rounded-xl focus:ring-4 focus:ring-blue-300 focus:border-blue-500 transition">{{ old('goal_this_week', $weeklyGoal->goal_this_week) }}</textarea>
                </div>

                {{-- 来週の予定 --}}
                <div>
                    <label for="plan_next_week" class="text-2xl font-bold text-gray-800 mb-4 block">
                        来週の予定
                    </label>
                    <textarea id="plan_next_week" name="plan_next_week" rows="6"
                        class="w-full text-lg p-4 border-2 border-gray-300 rounded-xl focus:ring-4 focus:ring-green-300 focus:border-green-500 transition">{{ old('plan_next_week', $weeklyGoal->plan_next_week) }}</textarea>
                </div>
            </div>

            {{-- ボタン --}}
            <div class="mt-12 flex justify-end gap-4">
                <a href="{{ route('weekly-reports.show', ['user' => $user, 'year' => $year, 'week_number' => $week_number]) }}"
                    class="px-8 py-4 text-lg font-bold bg-gray-200 hover:bg-gray-300 rounded-xl transition">
                    キャンセル
                </a>
                <button type="submit"
                    class="px-8 py-4 text-lg font-bold bg-blue-600 hover:bg-blue-700 text-white rounded-xl shadow-lg transition transform hover:-translate-y-1">
                    💾 保存する
                </button>
            </div>
        </form>
    </div>
</x-portal-layout>
