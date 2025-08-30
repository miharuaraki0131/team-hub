<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Team-hub') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin="" />
    <link rel="stylesheet" as="style" onload="this.rel='stylesheet'"
          href="https://fonts.googleapis.com/css2?display=swap&family=Noto+Sans+JP:wght@400;500;700&family=Inter:wght@400;500;700" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Inter', 'Noto Sans JP', sans-serif;
        }
    </style>
</head>
<body class="font-sans text-gray-900 antialiased">
    <div class="min-h-screen bg-gray-50 flex items-center justify-center">
        <div class="max-w-5xl w-full mx-auto p-4">
            <div class="grid grid-cols-1 md:grid-cols-2 bg-white rounded-2xl shadow-2xl overflow-hidden">

                <!-- 左側：画像エリア -->
                <div class="hidden md:block bg-cover bg-center"
                     style="background-image: url('{{ asset('images/header-background.jpg') }}');">
                    {{-- ヘッダーと同じ画像を表示 --}}
                </div>

                <!-- 右側：フォームエリア -->
                <div class="min-h-[450px] flex flex-col justify-center p-6 md:p-8">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </div>
</body>
</html>
