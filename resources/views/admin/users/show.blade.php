{{-- resources/views/admin/users/show.blade.php --}}

<x-portal-layout :showHero="false">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            ユーザー詳細
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden">

                {{-- ユーザー情報ヘッダー --}}
                <div class="p-6 md:p-8">
                    <div class="flex items-center space-x-5">
                        <img class="h-20 w-20 rounded-full object-cover mr-4"
                            src="https://i.pravatar.cc/150?u={{ $user->email }}" alt="User Avatar">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">{{ $user->name }}</h1>
                            <p class="text-md text-gray-600">{{ $user->division->name ?? '未設定' }}</p>
                            <span
                                class="mt-2 px-2 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full {{ $user->is_admin ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                {{ $user->is_admin ? '管理者' : '一般ユーザー' }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- 詳細情報 --}}
                <div class="border-t border-gray-200 px-6 md:px-8 py-6 space-y-4">
                    <h3 class="text-lg font-semibold text-gray-800">連絡先情報</h3>
                    <dl>
                        <div class="py-2 sm:grid sm:grid-cols-3 sm:gap-4">
                            <dt class="text-sm font-medium text-gray-500">メールアドレス</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $user->email }}</dd>
                        </div>
                        {{-- 他に表示したい情報があればここに追加 --}}
                    </dl>
                </div>

                {{-- アクションボタン --}}
                <div class="bg-gray-50 px-6 py-4 flex justify-end items-center gap-4">

                    {{-- 編集ボタン（プライマリアクションではないので、少し控えめに） --}}
                    <a href="{{ route('admin.users.edit', $user) }}"
                        class="inline-flex items-center justify-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                        編集
                    </a>

                    {{-- 削除フォーム --}}
                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST"
                        onsubmit="return confirm('本当にこのユーザーを削除しますか？関連するデータも削除される可能性があります。');">
                        @csrf
                        @method('DELETE')
                        {{-- ▼▼▼ このbuttonタグに、赤いボタンスタイルを適用 ▼▼▼ --}}
                        <button type="submit"
                            class="inline-flex items-center justify-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                            削除
                        </button>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-portal-layout>
