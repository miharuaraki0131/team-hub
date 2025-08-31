{{-- resources/views/components/textarea-card.blade.php --}}

@props([
    'label' => '',
    'name' => '',
    'value' => '',
    'rows' => 5,
])

<div class="w-full">
    {{-- ラベル：シンプルに下線のみ --}}
    <label class="block font-bold text-xl text-gray-800 mb-5 pb-2 border-b-3 border-blue-400">
        {{ $label }}
    </label>

    {{-- テキストエリア：大きな文字、高いコントラスト、見やすいフォーカス --}}
    <textarea
        name="{{ $name }}"
        rows="{{ $rows }}"
        class="w-full px-6 py-5 rounded-xl border-3 border-gray-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-200 text-lg leading-relaxed text-gray-900 placeholder-gray-500 resize-none overflow-hidden bg-white shadow-sm hover:shadow-md transition-all duration-200 font-medium"
        style="min-height: 150px;"
        oninput="this.style.height = ''; this.style.height = Math.max(150, this.scrollHeight) + 'px'"
        >{{ old($name, $value) }}</textarea>

    {{-- エラーメッセージ：見やすく大きく --}}
    @error($name)
        <div class="mt-3 p-3 bg-red-50 border-2 border-red-300 rounded-lg">
            <p class="text-red-700 text-base font-medium">⚠️ {{ $message }}</p>
        </div>
    @enderror
</div>
