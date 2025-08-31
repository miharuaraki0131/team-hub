{{-- resources/views/knowledges/index.blade.php --}}
<x-portal-layout :showHero="false">
    <div class="max-w-6xl mx-auto">
        {{-- ページヘッダー --}}
        <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-8 mb-8">
            <div class="flex flex-col sm:flex-row justify-between items-start">
                <div>
                    <h1 class="text-4xl font-bold text-gray-800 mb-2">📢 共有事項・お知らせ</h1>
                    <p class="text-lg text-gray-600">チーム全体の情報共有スペースです</p>
                </div>

                @auth
                    <div class="mt-4 sm:mt-0 flex gap-2">
                        <a href="{{ route('knowledges.create') }}"
                            class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold transition-all duration-200 shadow-md hover:shadow-lg">
                            <span class="mr-2">+</span>
                            <span>新規投稿</span>
                        </a>
                    </div>
                @endauth
            </div>
        </div>

        {{-- フラッシュメッセージ --}}
        <x-flash-message />

        {{-- 検索・フィルターエリア --}}
        <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6 mb-8">
            <form method="GET" action="{{ route('knowledges.index') }}" class="flex flex-col sm:flex-row gap-4">
                {{-- 検索ボックス --}}
                <div class="flex-1">
                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="タイトルや本文で検索..."
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-blue-400 focus:ring-2 focus:ring-blue-200 text-lg">
                </div>

                {{-- カテゴリーフィルター --}}
                <div class="sm:w-48">
                    <select name="category"
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-blue-400 focus:ring-2 focus:ring-blue-200 text-lg bg-white">
                        <option value="">全カテゴリー</option>
                        <option value="announcement" {{ request('category') === 'announcement' ? 'selected' : '' }}>お知らせ</option>
                        <option value="meeting" {{ request('category') === 'meeting' ? 'selected' : '' }}>議事録</option>
                        <option value="manual" {{ request('category') === 'manual' ? 'selected' : '' }}>マニュアル</option>
                        <option value="other" {{ request('category') === 'other' ? 'selected' : '' }}>その他</option>
                    </select>
                </div>

                {{-- 検索ボタン --}}
                <div class="flex gap-2">
                    <button type="submit"
                            class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-bold transition-all duration-200">
                        🔍 検索
                    </button>
                    @if(request('search') || request('category'))
                        <a href="{{ route('knowledges.index') }}"
                           class="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg font-bold transition-all duration-200">
                            クリア
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- 共有事項一覧 --}}
        <div class="space-y-6">
            @forelse($knowledges as $knowledge)
                <div class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden hover:shadow-xl transition-all duration-200 transform hover:-translate-y-1">
                    <div class="p-6">
                        <div class="flex justify-between items-start mb-4">
                            <div class="flex-1">
                                {{-- バッジエリア --}}
                                <div class="flex flex-wrap gap-2 mb-3">
                                    @if($knowledge->is_pinned)
                                        <span class="px-3 py-1 bg-red-500 text-white text-sm font-bold rounded-full">
                                            📌 重要
                                        </span>
                                    @endif
                                    @if($knowledge->category)
                                        <span class="px-3 py-1 bg-blue-500 text-white text-sm font-bold rounded-full">
                                            @switch($knowledge->category)
                                                @case('announcement') 📢 お知らせ @break
                                                @case('meeting') 🤝 議事録 @break
                                                @case('manual') 📖 マニュアル @break
                                                @default 📄 その他
                                            @endswitch
                                        </span>
                                    @endif
                                    @if(!$knowledge->isPublished())
                                        <span class="px-3 py-1 bg-gray-500 text-white text-sm font-bold rounded-full">
                                            🔒 非公開
                                        </span>
                                    @endif
                                </div>

                                {{-- タイトル --}}
                                <h2 class="text-2xl font-bold text-gray-800 mb-2 hover:text-blue-600 transition-colors">
                                    <a href="{{ route('knowledges.show', $knowledge) }}">
                                        {{ $knowledge->title }}
                                    </a>
                                </h2>

                                {{-- 要約 --}}
                                <p class="text-gray-600 leading-relaxed mb-4">
                                    {{ $knowledge->getExcerpt(150) }}
                                </p>

                                {{-- メタ情報 --}}
                                <div class="flex flex-wrap gap-4 text-sm text-gray-500">
                                    <div class="flex items-center">
                                        <span class="mr-1">👤</span>
                                        <span>{{ $knowledge->user->name }}</span>
                                    </div>
                                    <div class="flex items-center">
                                        <span class="mr-1">📅</span>
                                        <span>{{ $knowledge->created_at->format('Y/m/d') }}</span>
                                    </div>
                                    <div class="flex items-center">
                                        <span class="mr-1">👁️</span>
                                        <span>{{ $knowledge->view_count ?? 0 }}回表示</span>
                                    </div>
                                    @if($knowledge->expired_at && $knowledge->expired_at < now())
                                        <div class="flex items-center text-red-600 font-bold">
                                            <span class="mr-1">⚠️</span>
                                            <span>期限切れ</span>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- アクションボタン --}}
                            <div class="ml-4 flex flex-col gap-2">
                                <a href="{{ route('knowledges.show', $knowledge) }}"
                                   class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-bold text-sm transition-all duration-200">
                                    詳細を見る
                                </a>

                                @if(Auth::check() && (Auth::id() === $knowledge->user_id || Auth::user()->is_admin))
                                    <a href="{{ route('knowledges.edit', $knowledge) }}"
                                       class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg font-bold  transition-all duration-200 text-center">
                                        編集
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                {{-- 投稿がない場合の表示 --}}
                <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-12 text-center">
                    <div class="text-6xl mb-4">📝</div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-2">まだ投稿がありません</h3>
                    <p class="text-gray-600 mb-6">
                        @if(request('search') || request('category'))
                            検索条件に一致する投稿が見つかりませんでした。
                        @else
                            チームの情報を共有してみましょう！
                        @endif
                    </p>

                    @auth
                        @if(!request('search') && !request('category'))
                            <a href="{{ route('knowledges.create') }}"
                               class="inline-flex items-center px-8 py-4 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold text-lg transition-all duration-200 shadow-lg hover:shadow-xl">
                                <span class="mr-2">✨</span>
                                <span>最初の投稿をする</span>
                            </a>
                        @else
                            <a href="{{ route('knowledges.index') }}"
                               class="inline-flex items-center px-8 py-4 bg-blue-600 hover:bg-blue-700 text-white  rounded-xl font-bold text-lg transition-all duration-200">
                                <span class="mr-2">🔍</span>
                                <span>すべての投稿を見る</span>
                            </a>
                        @endif
                    @endauth
                </div>
            @endforelse
        </div>

        {{-- ペジネーション --}}
        @if($knowledges->hasPages())
            <div class="mt-12 flex justify-center">
                <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-4">
                    {{ $knowledges->withQueryString()->links() }}
                </div>
            </div>
        @endif
    </div>
</x-portal-layout>
