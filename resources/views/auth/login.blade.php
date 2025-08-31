<x-guest-layout>
    <!-- ロゴ -->
    <a href="/" class="flex items-center gap-3 mb-8">
        <x-application-logo class="w-20 h-20 fill-current text-gray-500" />
        <h2 class="text-gray-800 text-2xl font-bold">Team-hub</h2>
    </a>

    <h3 class="text-3xl font-bold text-gray-900">おかえりなさい</h3>
    <p class="text-gray-500 mt-2 mb-8">アカウント情報をご入力ください。</p>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf
        <div class="space-y-6">
            <!-- Email Address -->
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">メールアドレス</label>
                <div class="mt-1">
                    <input id="email" name="email" type="email" placeholder="your@email.com"
                           class="form-input w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-colors"
                           :value="old('email')" required autofocus autocomplete="username" />
                </div>
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <!-- Password -->
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">パスワード</label>
                <div class="mt-1">
                    <input id="password" name="password" type="password" placeholder="••••••••"
                           class="form-input w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-colors"
                           required autocomplete="current-password" />
                </div>
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <!-- Remember Me & Forgot Password -->
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input id="remember_me" name="remember" type="checkbox" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                    <label for="remember_me" class="ml-2 block text-sm text-gray-900">ログイン状態を保持する</label>
                </div>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-sm text-indigo-600 hover:text-indigo-500 hover:underline">
                        パスワードをお忘れですか？
                    </a>
                @endif
            </div>

            <!-- Login Button -->
            <div>
                <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                    ログイン
                </button>
            </div>
        </div>
    </form>

    {{-- Register Link --}}
    <div class="mt-6 text-center text-sm">
        <p class="text-gray-600">
            アカウントをお持ちでないですか？
            <a class="font-medium text-indigo-600 hover:text-indigo-500" href="{{ route('register') }}">
                新規登録
            </a>
        </p>
    </div>
</x-guest-layout>
