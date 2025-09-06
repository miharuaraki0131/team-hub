<x-portal-layout :showHero="false">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            部署管理
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- ヘッダー：タイトルと新規登録ボタン --}}
            <div class="flex flex-col sm:flex-row justify-between items-center mb-8">
                <h1 class="text-2xl font-bold text-gray-800 mb-4 sm:mb-0">
                    部署一覧
                </h1>
                <a href="{{ route('admin.divisions.create') }}"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold  text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition">
                    <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    新規部署登録
                </a>
            </div>

            {{-- 部署カードグリッド --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse ($divisions as $division)
                    <div
                        class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition-shadow duration-300">
                        <a href="{{ route('admin.divisions.show', $division) }}" class="block p-6">
                            <div class="flex items-center space-x-4 mb-4">
                                <img class="h-12 w-12 rounded-full object-cover"
                                    src="{{ $division->logo_url }}" alt="Division Logo">
                                <div class="min-w-0">
                                    <p class="text-lg font-bold text-gray-900 truncate">{{ $division->name }}</p>
                                    {{-- [変更] 部署に所属するユーザーの人数 --}}
                                    <p class="text-sm text-gray-500 truncate">{{ $division->users_count }}名のメンバーが所属
                                    </p>
                                </div>
                            </div>
                        </a>
                    </div>
                @empty
                    <div class="md:col-span-2 lg:col-span-3 text-center py-12">
                        <p class="text-gray-500">部署はまだ登録されていません。</p>
                    </div>
                @endforelse
            </div>

            {{-- ページネーション --}}
            <div class="mt-8">
                {{ $divisions->links() }}
            </div>

        </div>
    </div>
</x-portal-layout>
