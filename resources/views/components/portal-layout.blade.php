<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;500;700;900&display=swap"
        rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')

</head>

<body class="bg-gray-50 text-gray-800">
    <div class="flex flex-col min-h-screen">
        {{-- =============================================== --}}
        {{-- ヘッダー：これは全てのページで共通 --}}
        {{-- =============================================== --}}
        <header class="relative text-white">
            {{-- 背景レイヤー（画像とオーバーレイ） --}}
            <div class="absolute inset-0">
                <img src="{{ asset('images/teamhub-header.png') }}" alt="Header Background"
                    class="w-full h-full object-cover">
                {{-- オーバーレイ（半透明の黒） --}}
                {{-- この数値を調整すると濃さが変わります (例: bg-black/30, bg-black/50) --}}
                <div class="absolute inset-0 bg-black/40"></div>
            </div>

            {{-- コンテンツレイヤー（ナビゲーションやヒーロー） --}}
            {{-- z-10 から z-20 に変更（以前のコードとの整合性のため） --}}
            <div class="relative z-20">

                {{-- ナビゲーション --}}
                @include('layouts.portal-navigation')

                {{-- ヒーローセクション（条件付きで表示） --}}
                @if (isset($showHero) && $showHero)
                    <div class="container mx-auto px-6 py-12 md:py-20 text-center">
                        <h1 class="text-3xl md:text-5xl font-bold mb-4">
                            {{ $heroTitle ?? '備品をスマートに管理' }}
                        </h1>
                        <p class="text-base md:text-lg text-white/80 mb-8 max-w-2xl mx-auto">
                            {{ $heroSubtitle ?? '必要な備品をいつでも、どこからでも。Team-hubがあなたの仕事をサポートします。' }}
                        </p>

                        {{-- ヒーローボタン（条件付きで表示） --}}
                        @if (isset($showHeroButtons) && $showHeroButtons)
                            <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                                <a class="btn-primary w-full sm:w-auto"
                                    href="{{ route('equipments.index', ['status' => 'available']) }}">
                                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path clip-rule="evenodd"
                                            d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
                                            fill-rule="evenodd"></path>
                                    </svg>
                                    <span>備品を予約する</span>
                                </a>
                                <a class="btn-secondary w-full sm:w-auto" href="{{ route('my.reservations.index') }}">
                                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
                                        <path clip-rule="evenodd"
                                            d="M4 5a2 2 0 012-2h8a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 011-1h6a1 1 0 110 2H8a1 1 0 01-1-1zm1 4a1 1 0 100 2h4a1 1 0 100-2H8z"
                                            fill-rule="evenodd"></path>
                                    </svg>
                                    <span>予約履歴を見る</span>
                                </a>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </header>

        {{-- =============================================== --}}
        {{-- メインコンテンツ：ここがページ毎に差し変わる！ --}}
        {{-- =============================================== --}}
        <main class="container mx-auto px-4 md:px-6 lg:px-8 py-6 md:py-8 flex-grow">
            {{ $slot }}
        </main>

        {{-- =============================================== --}}
        {{-- スマホ用フッターナビゲーション：これも共通 --}}
        {{-- =============================================== --}}
        <nav
            class="bg-white lg:hidden fixed bottom-0 left-0 right-0 bg-[var(--card-bg)] border-t border-[var(--border-color)] flex justify-around p-2 z-30 shadow-[0_-2px_10px_rgba(0,0,0,0.05)]">
            {{-- ダッシュボード --}}
            <a href="{{ route('dashboard') }}"
                class="flex flex-col items-center p-2 text-[var(--text-light)] hover:text-[var(--primary-color)] transition-colors">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path
                        d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"
                        stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <span class="text-xs mt-1">ホーム</span>
            </a>

            {{-- 備品一覧 --}}
            <a href="#"
                class="flex flex-col items-center p-2 text-[var(--text-light)] hover:text-[var(--primary-color)] transition-colors">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-linecap="round"
                        stroke-linejoin="round" />
                </svg>
                <span class="text-xs mt-1">検索</span>
            </a>

            {{-- 予約管理 --}}
            <a href="#"
                class="flex flex-col items-center p-2 text-[var(--text-light)] hover:text-[var(--primary-color)] transition-colors">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                        stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <span class="text-xs mt-1">予約</span>
            </a>

            {{-- 通知 --}}
            <a href="#"
                class="flex flex-col items-center p-2 text-[var(--text-light)] hover:text-[var(--primary-color)] transition-colors relative">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path
                        d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
                        stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <span class="text-xs mt-1">通知</span>
                {{-- 通知バッジ --}}
                <span
                    class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-4 w-4 flex items-center justify-center">3</span>
            </a>

            {{-- プロフィール --}}
            <a href="{{ route('profile.edit') }}"
                class="flex flex-col items-center p-2 text-[var(--text-light)] hover:text-[var(--primary-color)] transition-colors">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" stroke-linecap="round"
                        stroke-linejoin="round" />
                </svg>
                <span class="text-xs mt-1">プロフィール</span>
            </a>
        </nav>

        {{-- スマホナビ用の余白を確保 --}}
        <div class="lg:hidden h-16"></div>
    </div>

    {{-- ページ固有のJavaScriptを読み込むための場所 --}}
    @stack('scripts')
</body>

</html>
