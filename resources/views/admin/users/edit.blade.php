<x-portal-layout :showHero="true" heroTitle="ユーザーの新規登録" heroSubtitle="新しいユーザー情報をシステムに登録します。">

    <div class="bg-[var(--card-bg)] p-6 md:p-8 rounded-xl shadow-md max-w-2xl mx-auto">

        <form action="{{ route('admin.users.update', $user) }}" method="POST" enctype="multipart/form-data">
            {{-- ★enctypeを追加 --}}
            @csrf
            @method('PUT')

            <div class="space-y-6">

                {{-- 氏名 --}}
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">氏名 <span
                            class="text-red-500">*</span></label>
                    <div class="mt-1">
                        <input type="text" name="name" id="name" required
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                            placeholder="例：山田 太郎" value="{{ old('name', $user->name) }}">
                        @error('name')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- 部署画像 --}}
                <div>
                    <label for="avatar" class="block text-sm font-medium text-gray-700">プロフィール画像</label>
                    <div class="mt-1">
                        <input type="file" name="avatar" id="avatar"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    </div>
                    @error('avatar')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- メールアドレス --}}
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">メールアドレス <span
                            class="text-red-500">*</span></label>
                    <div class="mt-1">
                        <input type="email" name="email" id="email" required
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                            placeholder="例：example@example.com" value="{{ old('email', $user->email) }}">
                        @error('email')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- パスワード --}}
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">パスワード </label>
                    <div class="mt-1">
                        <input type="password" name="password" id="password"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                            placeholder="変更する場合のみ入力">
                        @error('password')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- パスワード（確認用） --}}
                <div>
                    <label for="password_confirmation"
                        class="block text-sm font-medium text-gray-700">パスワード（確認用）</label>
                    <div class="mt-1">
                        <input type="password" name="password_confirmation" id="password_confirmation"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                            placeholder="変更するパスワードを再入力">
                        @error('password_confirmation')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- 所属部署 --}}
                <div>
                    <label for="division_id" class="block text-sm font-medium text-gray-700">所属部署</label>
                    <div class="mt-1">
                        <select id="division_id" name="division_id"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            <option value="">選択してください</option>
                            @foreach ($divisions as $division)
                                <option value="{{ $division->id }}"
                                    {{ old('division_id', $user->division_id) == $division->id ? 'selected' : '' }}>
                                    {{ $division->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- 権限 --}}
                <div class="flex items-start">
                    <div class="flex items-center h-5">
                        <input id="is_admin" name="is_admin" type="checkbox" value="1"
                            {{ old('is_admin', $user->is_admin) ? 'checked' : '' }}
                            class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                        @error('is_admin')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="ml-3 text-sm">
                        <label for="is_admin" class="font-medium text-gray-700">管理者として登録する</label>
                        <p class="text-gray-500">チェックすると、このユーザーに管理者権限が付与されます。</p>
                    </div>
                </div>

                {{-- 画像アップロード --}}
                <div>
                    <label for="image" class="block text-sm font-medium text-gray-700">プロフィール画像</label>
                    <div class="mt-1">
                        <input type="file" name="image" id="image"
                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        @error('image')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
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

</x-portal-layout>
