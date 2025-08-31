{{-- resources/views/knowledges/edit.blade.php --}}
<x-portal-layout :showHero="false">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-200">

            {{-- ヘッダー --}}
            <div class="p-8 bg-slate-100 border-b-2 border-gray-200">
                <div class="flex flex-col sm:flex-row justify-between items-start">
                    <div>
                        <h1 class="text-4xl font-bold text-gray-800 mb-2">✏️ 共有事項を編集</h1>
                        <p class="text-lg text-gray-600">「{{ Str::limit($knowledge->title, 40) }}」を編集中</p>
                        <p class=" text-gray-500 mt-2">
                            投稿者: {{ $knowledge->user->name }} |
                            作成日: {{ $knowledge->created_at->format('Y/m/d H:i') }}
                        </p>
                    </div>
                    <div class="mt-4 sm:mt-0 flex gap-2">
                        <a href="{{ route('knowledges.show', $knowledge) }}"
                            class="inline-block px-6 py-3 rounded-xl bg-white hover:bg-gray-100 border-2 border-gray-300 hover:border-gray-400 transition-all duration-200 text-lg font-bold text-gray-800 shadow-md hover:shadow-lg">
                            👁️ 表示
                        </a>
                        <a href="{{ route('knowledges.index') }}"
                            class="inline-block px-6 py-3 rounded-xl bg-white hover:bg-gray-100 border-2 border-gray-300 hover:border-gray-400 transition-all duration-200 text-lg font-bold text-gray-800 shadow-md hover:shadow-lg">
                            📋 一覧
                        </a>
                    </div>
                </div>
            </div>

            {{-- フォーム --}}
            <form method="POST" action="{{ route('knowledges.update', $knowledge) }}" class="flex flex-col">
                @csrf
                @method('PUT')

                <div class="p-8 md:p-12 flex-grow space-y-8">
                    {{-- フラッシュメッセージ --}}
                    <x-flash-message />

                    {{-- タイトル --}}
                    <div>
                        <label for="title"
                            class="block text-2xl font-bold text-gray-800 mb-4 pb-2 border-b-2 border-blue-400">
                            📝 タイトル
                        </label>
                        <input type="text" id="title" name="title"
                            value="{{ old('title', $knowledge->title) }}"
                            class="w-full px-6 py-4 text-xl border-2 border-gray-300 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-200 transition-all duration-200 font-medium"
                            placeholder="例：新しいプロジェクトについて" required>
                        @error('title')
                            <div class="mt-3 p-3 bg-red-50 border-2 border-red-300 rounded-lg">
                                <p class="text-red-700 text-base font-medium">⚠️ {{ $message }}</p>
                            </div>
                        @enderror
                    </div>

                    {{-- 本文 --}}
                    <div>
                        <label for="body"
                            class="block text-2xl font-bold text-gray-800 mb-4 pb-2 border-b-2 border-green-400">
                            📄 本文
                        </label>
                        <textarea id="body" name="body" rows="12"
                            class="w-full px-6 py-4 text-lg border-2 border-gray-300 rounded-xl focus:border-green-500 focus:ring-4 focus:ring-green-200 transition-all duration-200 font-medium leading-relaxed resize-none"
                            placeholder="チームメンバーに共有したい内容を自由に入力してください。" required>{{ old('body', $knowledge->body) }}</textarea>
                        @error('body')
                            <div class="mt-3 p-3 bg-red-50 border-2 border-red-300 rounded-lg">
                                <p class="text-red-700 text-base font-medium">⚠️ {{ $message }}</p>
                            </div>
                        @enderror
                    </div>

                    {{-- オプション設定 --}}
                    <div class="p-6 rounded-2xl border-2 border-gray-200">
                        <h3 class="text-xl font-bold text-gray-800 mb-6 pb-2 border-b-2 border-orange-400">
                            ⚙️ オプション設定
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- 固定表示 --}}
                            <div class="bg-white p-4 rounded-xl border border-gray-300">
                                <label class="flex items-center cursor-pointer">
                                    <input type="checkbox" name="is_pinned" value="1"
                                        {{ old('is_pinned', $knowledge->is_pinned) ? 'checked' : '' }}
                                        class="w-5 h-5 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2">
                                    <div class="ml-3">
                                        <div class="text-lg font-bold text-gray-800">📌 重要なお知らせとして固定表示</div>
                                        <div class=" text-gray-600">一覧の最上部に表示されます</div>
                                    </div>
                                </label>
                            </div>

                            {{-- カテゴリー --}}
                            <div class="bg-white p-4 rounded-xl border border-gray-300">
                                <label for="category" class="block text-lg font-bold text-gray-800 mb-2">
                                    🏷️ カテゴリー
                                </label>
                                <select id="category" name="category"
                                    class="w-full px-3 py-2 text-base border border-gray-300 rounded-lg focus:border-blue-400 focus:ring-2 focus:ring-blue-200 bg-white">
                                    <option value="">未分類</option>
                                    <option value="announcement"
                                        {{ old('category', $knowledge->category) === 'announcement' ? 'selected' : '' }}>
                                        お知らせ</option>
                                    <option value="meeting"
                                        {{ old('category', $knowledge->category) === 'meeting' ? 'selected' : '' }}>議事録
                                    </option>
                                    <option value="manual"
                                        {{ old('category', $knowledge->category) === 'manual' ? 'selected' : '' }}>
                                        マニュアル</option>
                                    <option value="other"
                                        {{ old('category', $knowledge->category) === 'other' ? 'selected' : '' }}>その他
                                    </option>
                                </select>
                            </div>
                        </div>

                        {{-- 公開期間設定 --}}
                        <div class="mt-6 bg-white p-4 rounded-xl border border-gray-300">
                            <h4 class="text-lg font-bold text-gray-800 mb-4">📅 公開期間設定（オプション）</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="published_at" class="block  font-bold text-gray-700 mb-2">
                                        🟢 公開開始日時
                                    </label>
                                    <input type="datetime-local" id="published_at" name="published_at"
                                        value="{{ old('published_at', $knowledge->published_at?->format('Y-m-d\TH:i')) }}"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:border-blue-400 focus:ring-2 focus:ring-blue-200">
                                    <p class="text-xs text-gray-500 mt-1">未設定の場合は即座に公開されます</p>
                                </div>
                                <div>
                                    <label for="expired_at" class="block  font-bold text-gray-700 mb-2">
                                        🔴 公開終了日時
                                    </label>
                                    <input type="datetime-local" id="expired_at" name="expired_at"
                                        value="{{ old('expired_at', $knowledge->expired_at?->format('Y-m-d\TH:i')) }}"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:border-blue-400 focus:ring-2 focus:ring-blue-200">
                                    <p class="text-xs text-gray-500 mt-1">未設定の場合は無期限で公開されます</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 統計情報 --}}
                    <div class="bg-blue-50 p-6 rounded-2xl border-2 border-blue-200">
                        <h3 class="text-xl font-bold text-gray-800 mb-4 pb-2 border-b-2 border-blue-400">
                            📊 統計情報
                        </h3>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                            <div class="bg-white p-3 rounded-lg border border-blue-200">
                                <div class="text-2xl font-bold text-blue-600">{{ $knowledge->view_count ?? 0 }}</div>
                                <div class=" text-gray-600">閲覧数</div>
                            </div>
                            <div class="bg-white p-3 rounded-lg border border-blue-200">
                                <div class="text-2xl font-bold text-green-600">
                                    {{ $knowledge->created_at->format('m/d') }}</div>
                                <div class=" text-gray-600">作成日</div>
                            </div>
                            <div class="bg-white p-3 rounded-lg border border-blue-200">
                                <div class="text-2xl font-bold text-orange-600">
                                    {{ $knowledge->updated_at->format('m/d') }}</div>
                                <div class=" text-gray-600">更新日</div>
                            </div>
                            <div class="bg-white p-3 rounded-lg border border-blue-200">
                                <div
                                    class="text-2xl font-bold {{ $knowledge->isPublished() ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $knowledge->isPublished() ? '公開中' : '非公開' }}
                                </div>
                                <div class=" text-gray-600">状態</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- フッター --}}
                <div class="bg-gray-50 px-8 py-6 flex justify-between items-center border-t-2 border-gray-200">
                    <div class="flex gap-2">
                        <a href="{{ route('knowledges.show', $knowledge) }}"
                            class="px-6 py-3 text-base font-bold bg-gray-200 hover:bg-gray-300 rounded-xl transition-all duration-200 text-gray-800">
                            キャンセル
                        </a>
                    </div>
                    <div class="flex gap-2">
                        {{-- 削除ボタン（管理者または投稿者のみ） --}}
                        @if (Auth::id() === $knowledge->user_id || Auth::user()->is_admin)
                            <button type="button" onclick="confirmDelete()"
                                class="px-6 py-3 text-base font-bold bg-red-600 hover:bg-red-700 text-white rounded-xl transition-all duration-200">
                                🗑️ 削除
                            </button>
                        @endif
                        <button type="submit"
                            class="px-12 py-4 text-lg font-bold bg-blue-600 hover:bg-blue-700 text-white rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 transform hover:-translate-y-1">
                            💾 更新する
                        </button>
                    </div>
                </div>
            </form>

            {{-- 削除用の隠しフォーム --}}
            @if (Auth::id() === $knowledge->user_id || Auth::user()->is_admin)
                <form id="delete-form" action="{{ route('knowledges.destroy', $knowledge) }}" method="POST"
                    style="display: none;">
                    @csrf
                    @method('DELETE')
                </form>
            @endif
        </div>
    </div>

    {{-- 削除確認のJavaScript --}}
    <script>
        function confirmDelete() {
            if (confirm('本当にこの共有事項を削除しますか？\n\n削除した内容は復旧できません。')) {
                document.getElementById('delete-form').submit();
            }
        }
    </script>
</x-portal-layout>
