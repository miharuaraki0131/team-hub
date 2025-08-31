{{-- resources/views/events/create.blade.php --}}
<x-portal-layout :showHero="false">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-200">

            {{-- ヘッダー --}}
            <div class="p-8 bg-slate-100 border-b-2 border-gray-200">
                <div class="flex flex-col sm:flex-row justify-between items-start">
                    <div>
                        <h1 class="text-4xl font-bold text-gray-800 mb-2">📅 予定の作成</h1>
                        <p class="text-lg text-gray-600">チームや個人の予定をカレンダーに登録しましょう</p>
                    </div>
                    <a href="{{ route('events.index') }}"
                        class="mt-4 sm:mt-0 inline-block px-6 py-3 rounded-xl bg-white hover:bg-gray-100 border-2 border-gray-300 hover:border-gray-400 transition-all duration-200 text-lg font-bold text-gray-800 shadow-md hover:shadow-lg">
                        カレンダーに戻る
                    </a>
                </div>
            </div>

            {{-- フォーム --}}
            <form method="POST" action="{{ route('events.store') }}" class="flex flex-col">
                @csrf

                <div class="p-8 md:p-12 flex-grow space-y-8">
                    {{-- フラッシュメッセージ --}}
                    <x-flash-message />

                    {{-- タイトル --}}
                    <div>
                        <label for="title"
                            class="block text-2xl font-bold text-gray-800 mb-4 pb-2 border-b-2 border-blue-400">
                            📝 予定のタイトル
                        </label>
                        <input type="text" id="title" name="title" value="{{ old('title') }}"
                            class="w-full px-6 py-4 text-xl border-2 border-gray-300 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-200 transition-all duration-200 font-medium"
                            placeholder="例：TeamHub Ver.2.0 定例ミーティング" required>
                        @error('title')
                            <div class="mt-3 p-3 bg-red-50 border-2 border-red-300 rounded-lg">
                                <p class="text-red-700 text-base font-medium">⚠️ {{ $message }}</p>
                            </div>
                        @enderror
                    </div>

                    {{-- 日時設定 --}}
                    <div class="p-6 rounded-2xl border-2 border-gray-200">
                        <h3 class="text-xl font-bold text-gray-800 mb-6 pb-2 border-b-2 border-green-400">
                            ⏰ 日時設定
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="block text-lg font-bold text-gray-800">🟢 開始</label>
                                <input type="date" name="start_date" value="{{ old('start_date', $defaultDate) }}"
                                    class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg text-base focus:border-green-500 focus:ring-2 focus:ring-green-200">
                                <input type="time" name="start_time" value="{{ old('start_time', $defaultTime) }}"
                                    class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg text-base focus:border-green-500 focus:ring-2 focus:ring-green-200">
                                @error('start_date')
                                    <div
                                        class="mt-1 p-2 bg-red-50 border border-red-300 rounded-lg text-red-700 text-sm font-medium">
                                        ⚠️ {{ $message }}</div>
                                @enderror
                                @error('start_time')
                                    <div
                                        class="mt-1 p-2 bg-red-50 border border-red-300 rounded-lg text-red-700 text-sm font-medium">
                                        ⚠️ {{ $message }}</div>
                                @enderror
                            </div>
                            <div class="space-y-2">
                                <label class="block text-lg font-bold text-gray-800">🔴 終了</label>
                                <input type="date" name="end_date" value="{{ old('end_date', $defaultDate) }}"
                                    class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg text-base focus:border-red-500 focus:ring-2 focus:ring-red-200">
                                <input type="time" name="end_time"
                                    value="{{ old('end_time', now()->addHour()->format('H:i')) }}"
                                    class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg text-base focus:border-red-500 focus:ring-2 focus:ring-red-200">
                                @error('end_date')
                                    <div
                                        class="mt-1 p-2 bg-red-50 border border-red-300 rounded-lg text-red-700 text-sm font-medium">
                                        ⚠️ {{ $message }}</div>
                                @enderror
                                @error('end_time')
                                    <div
                                        class="mt-1 p-2 bg-red-50 border border-red-300 rounded-lg text-red-700 text-sm font-medium">
                                        ⚠️ {{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="mt-6 bg-white p-4 rounded-xl border border-gray-300">
                            <label class="flex items-center cursor-pointer">
                                <input type="checkbox" name="is_all_day" value="1"
                                    {{ old('is_all_day') ? 'checked' : '' }}
                                    class="w-5 h-5 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2">
                                <div class="ml-3">
                                    <div class="text-lg font-bold text-gray-800">終日の予定にする</div>
                                    <div class="text-sm text-gray-600">チェックすると、時間は無視されます</div>
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- 詳細 --}}
                    <div>
                        <label for="body"
                            class="block text-2xl font-bold text-gray-800 mb-4 pb-2 border-b-2 border-purple-400">
                            📄 詳細（オプション）
                        </label>
                        <textarea id="body" name="body" rows="6"
                            class="w-full px-6 py-4 text-lg border-2 border-gray-300 rounded-xl focus:border-purple-500 focus:ring-4 focus:ring-purple-200 font-medium leading-relaxed resize-none"
                            placeholder="ミーティングのアジェンダ、場所、ZoomのURLなど、補足情報を入力します">{{ old('body') }}</textarea>
                    </div>

                    {{-- オプション設定 --}}
                    <div class="p-6 rounded-2xl border-2 border-gray-200">
                        <h3 class="text-xl font-bold text-gray-800 mb-6 pb-2 border-b-2 border-orange-400">
                            ⚙️ オプション設定
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="bg-white p-4 rounded-xl border border-gray-300">
                                <label for="visibility" class="block text-lg font-bold text-gray-800 mb-2">👀
                                    公開範囲</label>
                                <select id="visibility" name="visibility"
                                    class="w-full px-3 py-2 text-base border border-gray-300 rounded-lg focus:border-blue-400 focus:ring-2 focus:ring-blue-200 bg-white">
                                    <option value="public"
                                        {{ old('visibility', 'public') === 'public' ? 'selected' : '' }}>全員に公開</option>
                                    <option value="private" {{ old('visibility') === 'private' ? 'selected' : '' }}>
                                        自分のみ</option>
                                </select>
                            </div>
                            <div class="bg-white p-4 rounded-xl border border-gray-300">
                                <label for="category" class="block text-lg font-bold text-gray-800 mb-2">🏷️
                                    カテゴリー</label>
                                <input type="text" id="category" name="category" value="{{ old('category') }}"
                                    class="w-full px-3 py-2 text-base border border-gray-300 rounded-lg focus:border-blue-400 focus:ring-2 focus:ring-blue-200"
                                    placeholder="例: 定例会議">
                            </div>
                            <div class="bg-white p-4 rounded-xl border border-gray-300">
                                <label class="block text-lg font-bold text-gray-800 mb-4">🎨 カラー</label>

                                {{-- [ここからが、新しいカラーパレットUI] --}}
                                <div class="flex flex-wrap gap-3">
                                    {{-- AppServiceProviderから共有された$colorPaletteをループ処理 --}}
                                    @foreach ($colorPalette as $hex => $name)
                                        <label class="cursor-pointer" title="{{ $name }}">
                                            {{-- ラジオボタン本体は、スクリーンリーダーのために残しつつ、視覚的には隠す --}}
                                            <input type="radio" name="color" value="{{ $hex }}"
                                                class="sr-only peer" {{-- [ここが重要] createとeditの両方で使える、完璧な値の設定 --}}
                                                @if (old('color', $event->color ?? '#bfdbfe') === $hex) checked @endif>

                                            {{-- peer-checked を使って、選択されたボタン（div）のスタイルを動的に変える --}}
                                            <div class="w-8 h-8 rounded-full border-2 border-white
                                                        peer-checked:ring-4 peer-checked:ring-offset-1 peer-checked:ring-blue-500
                                                        transition-all"
                                                style="background-color: {{ $hex }};">
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                                @error('color')
                                    <div class="mt-2 text-red-700 text-sm font-medium">⚠️ {{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- フッター --}}
                <div class="bg-gray-50 px-8 py-6 flex justify-center gap-4 border-t-2 border-gray-200">
                    <a href="{{ route('events.index') }}"
                        class="px-8 py-4 text-lg font-bold bg-gray-200 hover:bg-gray-300 rounded-xl transition-all duration-200 text-gray-800">
                        キャンセル
                    </a>
                    <button type="submit"
                        class="px-12 py-4 text-lg font-bold bg-blue-600 hover:bg-blue-700 text-white rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 transform hover:-translate-y-1">
                        💾 予定を保存する
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-portal-layout>
