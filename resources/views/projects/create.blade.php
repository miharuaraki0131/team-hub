{{-- resources/views/projects/create.blade.php --}}
<x-portal-layout>
    <div class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden">
        {{-- ヘッダー --}}
        <div class="bg-slate-100 border-b-2 border-gray-200 p-6">
            <h1 class="text-3xl font-bold text-gray-800">📁 新規プロジェクト作成</h1>
            <p class="text-gray-600 mt-1">TeamHub でタスク管理を始めましょう</p>
        </div>

        <div class="p-8">
            {{-- フラッシュメッセージ --}}
            <x-flash-message />

            <form action="{{ route('projects.store') }}" method="POST" class="max-w-2xl">
                @csrf

                <div class="space-y-6">
                    {{-- プロジェクト名 --}}
                    <div>
                        <label for="name" class="block text-lg font-bold text-gray-700 mb-3">
                            プロジェクト名 <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               id="name"
                               name="name"
                               value="{{ old('name') }}"
                               required
                               placeholder="例：TeamHub Ver.2.0 開発"
                               class="w-full p-4 text-lg border-2 border-gray-300 rounded-xl focus:ring-4 focus:ring-blue-300 focus:border-blue-500 transition @error('name') border-red-500 @enderror">
                        @error('name')
                            <p class="mt-2 text-red-600 text-sm">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- プロジェクト説明 --}}
                    <div>
                        <label for="description" class="block text-lg font-bold text-gray-700 mb-3">
                            プロジェクト説明
                        </label>
                        <textarea id="description"
                                  name="description"
                                  rows="5"
                                  placeholder="プロジェクトの概要、目標、背景などを記載してください。&#10;例：チーム内の業務プロセスを統合し、生産性とコラボレーションを向上させる統合業務プラットフォームの開発"
                                  class="w-full p-4 text-lg border-2 border-gray-300 rounded-xl focus:ring-4 focus:ring-blue-300 focus:border-blue-500 transition @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
                        @error('description')
                            <p class="mt-2 text-red-600 text-sm">{{ $message }}</p>
                        @enderror
                        <p class="mt-2 text-sm text-gray-500">1000文字以内で入力してください</p>
                    </div>
                </div>

                {{-- 送信ボタン --}}
                <div class="flex justify-end gap-4 mt-8">
                    <a href="{{ route('projects.index') }}"
                       class="px-8 py-4 text-lg font-bold bg-gray-300 hover:bg-gray-400 rounded-xl transition">
                        キャンセル
                    </a>
                    <button type="submit"
                            class="px-8 py-4 text-lg font-bold bg-blue-600 hover:bg-blue-700 text-white rounded-xl shadow-lg transition transform hover:-translate-y-1">
                        🚀 プロジェクトを作成
                    </button>
                </div>
            </form>

            {{-- プロジェクト作成後の流れ --}}
            <div class="mt-12 p-6 bg-blue-50 rounded-xl border-2 border-blue-200">
                <h3 class="text-lg font-bold text-blue-800 mb-4">📋 プロジェクト作成後の流れ</h3>
                <div class="space-y-3 text-blue-700">
                    <div class="flex items-start">
                        <span class="flex-shrink-0 w-8 h-8 bg-blue-200 rounded-full flex items-center justify-center text-sm font-bold mr-3">1</span>
                        <div>
                            <div class="font-bold">WBS/ガントチャート画面へ移動</div>
                            <div class="text-sm">プロジェクト作成後、WBS/ガントチャート機能でタスクの追加・管理を行います</div>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <span class="flex-shrink-0 w-8 h-8 bg-blue-200 rounded-full flex items-center justify-center text-sm font-bold mr-3">2</span>
                        <div>
                            <div class="font-bold">親タスク（フェーズ）の作成</div>
                            <div class="text-sm">大きな作業単位をまず親タスクとして登録します</div>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <span class="flex-shrink-0 w-8 h-8 bg-blue-200 rounded-full flex items-center justify-center text-sm font-bold mr-3">3</span>
                        <div>
                            <div class="font-bold">子タスクの追加</div>
                            <div class="text-sm">親タスクの「子追加」ボタンから、具体的な作業タスクを追加します</div>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <span class="flex-shrink-0 w-8 h-8 bg-blue-200 rounded-full flex items-center justify-center text-sm font-bold mr-3">4</span>
                        <div>
                            <div class="font-bold">担当者・スケジュール設定</div>
                            <div class="text-sm">各タスクに担当者、開始・終了予定日、予定工数を設定します</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-portal-layout>
