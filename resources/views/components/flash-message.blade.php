{{-- resources/views/components/flash-message.blade.php --}}

@props(['message'])

<div x-data="{ show: true }" x-show="show" x-transition:leave="transition ease-in duration-300"
    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

    {{-- 成功メッセージ (success) --}}
    @if (session('success'))
        <div
            class="mb-6 rounded-lg bg-green-100 p-4 text-base text-green-800 border border-green-200 flex justify-between items-center shadow">
            <p class="font-medium">{{ session('success') }}</p>
            <button @click="show = false" class="text-green-800 hover:text-green-900 font-bold text-xl">&times;</button>
        </div>
    @endif

    {{-- ... (エラー、警告、情報メッセージも、同様に、少しデザインをリッチにしておきました) ... --}}
    @if (session('error'))
        <div
            class="mb-6 rounded-lg bg-red-100 p-4 text-base text-red-800 border border-red-200 flex justify-between items-center shadow">
            <p class="font-medium">{{ session('error') }}</p>
            <button @click="show = false" class="text-red-800 hover:text-red-900 font-bold text-xl">&times;</button>
        </div>
    @endif

</div>
