{{-- resources/views/events/show.blade.php --}}
<x-portal-layout :showHero="false">
    <div class="max-w-4xl mx-auto py-12">
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-200">

            {{-- ヘッダー --}}
            <div class="p-8 bg-slate-100 border-b-2 border-gray-200"
                style="background-color: {{ $event->color }}20; border-color: {{ $event->color }}80;">
                <div class="flex flex-col sm:flex-row justify-between items-start">
                    <div class="flex-1">
                        {{-- カテゴリーバッジ --}}
                        @if ($event->category)
                            <div class="inline-block px-3 py-1 text-white font-bold rounded-full mb-3 text-sm"
                                style="background-color: {{ $event->color }};">
                                🏷️ {{ $event->category }}
                            </div>
                        @endif

                        {{-- 公開設定バッジ --}}
                        @if ($event->visibility === 'private')
                            <div
                                class="inline-block px-3 py-1 bg-gray-500 text-white font-bold rounded-full mb-3 text-sm ml-2">
                                🔒 自分のみ
                            </div>
                        @endif

                        {{-- タイトル --}}
                        <h1 class="text-4xl font-bold text-gray-800 mb-3">{{ $event->title }}</h1>

                        {{-- メタ情報 --}}
                        <div class="flex flex-wrap gap-x-6 gap-y-2 text-gray-600">
                            <div class="flex items-center">
                                <span class="mr-2">👤</span>
                                <span class="font-medium">{{ $event->user->name }}</span>
                            </div>
                            <div class="flex items-center">
                                <span class="mr-2">📅</span>
                                <span>{{ $event->created_at->format('Y/m/d H:i') }}作成</span>
                            </div>
                            @if ($event->created_at != $event->updated_at)
                                <div class="flex items-center">
                                    <span class="mr-2">✏️</span>
                                    <span>{{ $event->updated_at->format('Y/m/d H:i') }}更新</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- アクションボタン --}}
                    <div class="mt-4 sm:mt-0 flex flex-col sm:flex-row gap-2">
                        @if (Auth::check() && Auth::id() === $event->user_id)
                            <a href="{{ route('events.edit', $event) }}"
                                class="inline-block px-6 py-3 rounded-xl bg-blue-500 hover:bg-blue-600 text-white font-bold transition-all duration-200 shadow-md hover:shadow-lg text-center">
                                ✏️ 編集
                            </a>
                        @endif
                        <a href="{{ route('events.index') }}"
                            class="inline-block px-6 py-3 rounded-xl bg-white hover:bg-gray-100 border-2 border-gray-300 hover:border-gray-400 transition-all duration-200 font-bold text-gray-800 shadow-md hover:shadow-lg text-center">
                            カレンダーに戻る
                        </a>
                    </div>
                </div>
            </div>

            {{-- メインコンテンツ --}}
            <div class="p-8 md:p-12">
                {{-- フラッシュメッセージ --}}
                <x-flash-message />

                {{-- [ここから大幅に変更] 予定の日時と詳細 --}}

                {{-- 日時情報 --}}
                <div class="mb-8">
                    <h3 class="text-xl font-bold text-gray-800 mb-3 flex items-center">
                        <span class="mr-3 text-2xl">⏰</span>日時
                    </h3>
                    <div class="bg-gray-50 rounded-xl border h-24 flex items-center px-6 py-3">
                        <p class="text-2xl font-bold text-gray-800">
                            {{ $event->getFormattedDuration() }}
                        </p>
                    </div>
                </div>

                {{-- 詳細（本文） --}}
                @if ($event->body)
                    <div class="mb-8">
                        <h3 class="text-xl font-bold text-gray-800 mb-3 flex items-center">
                            <span class="mr-3 text-2xl">📄</span>詳細
                        </h3>
                        <div class="prose prose-lg max-w-none">
                            <div
                                class="text-lg leading-relaxed text-gray-800 whitespace-pre-line p-6 bg-gray-50 rounded-xl border">
                                {{ $event->body }}
                            </div>
                        </div>
                    </div>
                @endif

                {{-- [追加] 削除ボタン --}}
                @if (Auth::check() && (Auth::id() === $event->user_id || Auth::user()->is_admin))
                    <div class="mt-12 pt-8 border-t-2 border-dashed border-red-300">
                        <p class="text-gray-600 mb-4">この予定を削除します。この操作は元に戻せません。</p>
                        <form method="POST" action="{{ route('events.destroy', $event) }}"
                            onsubmit="return confirm('本当にこの予定を削除しますか？');">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="px-8 py-3 bg-red-600 hover:bg-red-700 text-white rounded-xl font-bold transition-all duration-200 shadow-md hover:shadow-lg">
                                🗑️ 削除する
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-portal-layout>
