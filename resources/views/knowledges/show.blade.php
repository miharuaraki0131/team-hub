{{-- resources/views/knowledges/show.blade.php --}}
<x-portal-layout :showHero="false">
    <div class="max-w-6xl mx-auto">
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-200">

            {{-- ヘッダー --}}
            <div class="p-8 bg-slate-100 border-b-2 border-gray-200">
                <div class="flex flex-col sm:flex-row justify-between items-start">
                    <div class="flex-1">
                        {{-- 固定表示バッジ --}}
                        @if ($knowledge->is_pinned)
                            <div class="inline-block px-3 py-1 bg-red-500 text-white  font-bold rounded-full mb-3">
                                📌 重要なお知らせ
                            </div>
                        @endif

                        {{-- カテゴリーバッジ --}}
                        @if ($knowledge->category)
                            <div class="inline-block px-3 py-1 bg-blue-500 text-white  font-bold rounded-full mb-3 ml-2">
                                🏷️
                                @switch($knowledge->category)
                                    @case('announcement')
                                        お知らせ
                                    @break

                                    @case('meeting')
                                        議事録
                                    @break

                                    @case('manual')
                                        マニュアル
                                    @break

                                    @default
                                        その他
                                @endswitch
                            </div>
                        @endif

                        <h1 class="text-4xl font-bold text-gray-800 mb-3">{{ $knowledge->title }}</h1>

                        {{-- メタ情報 --}}
                        <div class="flex flex-wrap gap-4 text-gray-600">
                            <div class="flex items-center">
                                <span class="mr-2">👤</span>
                                <span class="font-medium">{{ $knowledge->user->name }}</span>
                            </div>
                            <div class="flex items-center">
                                <span class="mr-2">📅</span>
                                <span>{{ $knowledge->created_at->format('Y/m/d H:i') }}</span>
                            </div>
                            @if ($knowledge->created_at != $knowledge->updated_at)
                                <div class="flex items-center">
                                    <span class="mr-2">✏️</span>
                                    <span>{{ $knowledge->updated_at->format('Y/m/d H:i') }}更新</span>
                                </div>
                            @endif
                            <div class="flex items-center">
                                <span class="mr-2">👁️</span>
                                <span>{{ $knowledge->view_count ?? 0 }}回表示</span>
                            </div>
                        </div>
                    </div>

                    {{-- アクションボタン --}}
                    <div class="mt-4 sm:mt-0 flex gap-2">
                        {{-- 編集・削除ボタン（投稿者または管理者のみ） --}}
                        @if (Auth::check() && (Auth::id() === $knowledge->user_id || Auth::user()->is_admin))
                            <a href="{{ route('knowledges.edit', $knowledge) }}"
                                class="inline-block px-6 py-3 rounded-xl bg-blue-500 hover:bg-blue-600 text-white font-bold transition-all duration-200 shadow-md hover:shadow-lg">
                                ✏️ 編集
                            </a>
                        @endif

                        <a href="{{ route('knowledges.index') }}"
                            class="inline-block px-6 py-3 rounded-xl bg-white hover:bg-gray-100 border-2 border-gray-300 hover:border-gray-400 transition-all duration-200 text-lg font-bold text-gray-800 shadow-md hover:shadow-lg">
                            📋 一覧に戻る
                        </a>
                    </div>
                </div>
            </div>

            {{-- メインコンテンツ --}}
            <div class="p-8 md:p-12">
                {{-- フラッシュメッセージ --}}
                <x-flash-message />

                {{-- 公開期間の表示 --}}
                @if ($knowledge->published_at || $knowledge->expired_at)
                    <div class="bg-blue-50 p-4 rounded-xl border-2 border-blue-200 mb-8">
                        <h3 class="text-lg font-bold text-blue-800 mb-2">📅 公開期間</h3>
                        <div class="text-blue-700">
                            @if ($knowledge->published_at)
                                <div>🟢 公開開始: {{ $knowledge->published_at->format('Y/m/d H:i') }}</div>
                            @endif
                            @if ($knowledge->expired_at)
                                <div>🔴 公開終了: {{ $knowledge->expired_at->format('Y/m/d H:i') }}</div>
                                @if ($knowledge->expired_at < now())
                                    <div class="mt-2 px-3 py-1 bg-red-500 text-white  font-bold rounded inline-block">
                                        ⚠️ 公開期間終了
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>
                @endif

                {{-- 本文 --}}
                <div class="prose prose-lg max-w-none">
                    <div class="text-lg leading-relaxed text-gray-800 whitespace-pre-line">
                        {{ $knowledge->body }}
                    </div>
                </div>

                {{-- 統計情報 --}}
                <div class="mt-12 bg-gray-50 p-6 rounded-2xl border-2 border-gray-200">
                    <h3 class="text-xl font-bold text-gray-800 mb-4">📊 統計情報</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                        <div class="bg-white p-4 rounded-lg border border-gray-200">
                            <div class="text-3xl font-bold text-blue-600">{{ $knowledge->view_count ?? 0 }}</div>
                            <div class=" text-gray-600 font-medium">閲覧数</div>
                        </div>
                        <div class="bg-white p-4 rounded-lg border border-gray-200">
                            <div class="text-3xl font-bold text-green-600">
                                {{ $knowledge->created_at->format('m/d') }}</div>
                            <div class=" text-gray-600 font-medium">作成日</div>
                        </div>
                        <div class="bg-white p-4 rounded-lg border border-gray-200">
                            <div class="text-3xl font-bold text-orange-600">
                                {{ $knowledge->updated_at->format('m/d') }}</div>
                            <div class=" text-gray-600 font-medium">最終更新</div>
                        </div>
                        <div class="bg-white p-4 rounded-lg border border-gray-200">
                            <div
                                class="text-3xl font-bold {{ $knowledge->isPublished() ? 'text-green-600' : 'text-red-600' }}">
                                {{ $knowledge->isPublished() ? '公開中' : '非公開' }}
                            </div>
                            <div class=" text-gray-600 font-medium">状態</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-portal-layout>
