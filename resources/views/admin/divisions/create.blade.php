<x-portal-layout :showHero="true" heroTitle="部署の新規登録" heroSubtitle="新しい部署情報をシステムに登録します。">

    <div class="bg-[var(--card-bg)] p-6 md:p-8 rounded-xl shadow-md max-w-2xl mx-auto">

        <form action="{{ route('admin.divisions.store') }}" method="POST" enctype="multipart/form-data">
            {{-- ★enctypeを追加 --}}
            @csrf

            <div class="space-y-6">

                {{-- 部署名 --}}
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">部署名 <span
                            class="text-red-500">*</span></label>
                    <div class="mt-1">
                        <input type="text" name="name" id="name" required
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                            placeholder="例：開発部" value="{{ old('name') }}">
                        @error('name')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- 部署画像 --}}
                <div>
                    <label for="logo" class="block text-sm font-medium text-gray-700">プロフィール画像</label>
                    <div class="mt-1">
                        <input type="file" name="logo" id="logo"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    </div>
                    @error('logo')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- 通知先 --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700">通知先メールアドレス</label>
                    <div id="email-inputs-container" class="mt-1 space-y-2">
                        {{-- バリデーションエラーで戻ってきた場合、入力値を復元 --}}
                        @if (old('emails'))
                            @foreach (old('emails') as $email)
                                <input type="email" name="emails[]" value="{{ $email }}"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                    placeholder="例: team@example.com">
                            @endforeach
                            {{-- 通常の編集画面表示 --}}
                        @else
                            @forelse ($division->notificationDestinations as $destination)
                                <input type="email" name="emails[]" value="{{ $destination->email }}"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            @empty
                                {{-- 既存の通知先がない場合は、空の入力欄を1つ表示 --}}
                                <input type="email" name="emails[]"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                    placeholder="例: team@example.com">
                            @endforelse
                        @endif
                    </div>
                    {{-- バリデーションエラー（配列）の表示 --}}
                    @error('emails.*')
                        <p class="text-red-500 text-sm mt-1">⚠️ {{ $message }}</p>
                    @enderror

                    <button type="button" id="add-email-button"
                        class="mt-2 text-sm font-semibold text-blue-600 hover:text-blue-800">
                        ＋ 入力欄を追加
                    </button>
                </div>
            </div>

            {{-- 登録ボタン --}}
            <div class="mt-8 pt-5 border-t border-gray-200">
                <div class="flex justify-end">
                    <a href="{{ route('admin.users.index') }}" class="btn-secondary mr-4">
                        キャンセル
                    </a>
                    <button type="submit" class="btn-primary">
                        登録する
                    </button>
                </div>
            </div>

        </form>

    </div>

    @push('scripts')
        <script>
            document.getElementById('add-email-button').addEventListener('click', function() {
                const container = document.getElementById('email-inputs-container');
                const newEmailInput = document.createElement('input');
                newEmailInput.type = 'email';
                newEmailInput.name = 'emails[]'; // 配列として送信するための []
                newEmailInput.placeholder = '例: team@example.com';
                newEmailInput.className =
                    'block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm mt-2'; // mt-2を追加
                container.appendChild(newEmailInput);
            });
        </script>
    @endpush

</x-portal-layout>
