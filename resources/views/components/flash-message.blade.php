{{-- resources/views/components/flash-message.blade.php --}}

@props(['message'])

<div x-data="{ show: true }" x-show="show" x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

    {{-- 成功メッセージ (success) --}}
    @if (session('success'))
        <div class="mb-4 rounded-lg bg-green-100 p-4 text-sm text-green-700 border border-green-200 flex justify-between items-center">
            <p>{{ session('success') }}</p>
            <button @click="show = false" class="text-green-700 hover:text-green-900">&times;</button>
        </div>
    @endif

    {{-- エラーメッセージ (error) --}}
    @if (session('error'))
        <div class="mb-4 rounded-lg bg-red-100 p-4 text-sm text-red-700 border border-red-200 flex justify-between items-center">
            <p>{{ session('error') }}</p>
            <button @click="show = false" class="text-red-700 hover:text-red-900">&times;</button>
        </div>
    @endif

    {{-- 警告メッセージ (warning) --}}
    @if (session('warning'))
        <div class="mb-4 rounded-lg bg-yellow-100 p-4 text-sm text-yellow-700 border border-yellow-200 flex justify-between items-center">
            <p>{{ session('warning') }}</p>
            <button @click="show = false" class="text-yellow-700 hover:text-yellow-900">&times;</button>
        </div>
    @endif

    {{-- 情報メッセージ (info) --}}
    @if (session('info'))
        <div class="mb-4 rounded-lg bg-blue-100 p-4 text-sm text-blue-700 border border-blue-200 flex justify-between items-center">
            <p>{{ session('info') }}</p>
            <button @click="show = false" class="text-blue-700 hover:text-blue-900">&times;</button>
        </div>
    @endif

</div>
