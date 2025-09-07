{{-- resources/views/projects/index.blade.php --}}
<x-portal-layout>
    <div class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden">
        {{-- ヘッダー --}}
        <div class="bg-slate-100 border-b-2 border-gray-200 p-6">
            <div class="flex justify-between items-center">
                <h1 class="text-3xl font-bold text-gray-800">📁 プロジェクト一覧</h1>
                <a href="{{ route('projects.create') }}"
                   class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-bold transition shadow-md">
                    ➕ 新規プロジェクト
                </a>
            </div>
        </div>

        <div class="p-6">
            {{-- フラッシュメッセージ --}}
            <x-flash-message />

            {{-- プロジェクト一覧 --}}
            @if($projects->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($projects as $project)
                        <div class="bg-white border-2 border-gray-200 rounded-xl p-6 hover:shadow-lg transition-all duration-200 hover:border-blue-300">
                            {{-- プロジェクト名 --}}
                            <h3 class="text-xl font-bold text-gray-800 mb-3">{{ $project->name }}</h3>

                            {{-- プロジェクト説明 --}}
                            <p class="text-gray-600 mb-4 line-clamp-3">
                                {{ $project->description ?? 'プロジェクトの説明が未設定です。' }}
                            </p>

                            {{-- プロジェクト統計 --}}
                            <div class="grid grid-cols-2 gap-3 mb-4">
                                <div class="bg-blue-50 p-3 rounded-lg border border-blue-200">
                                    <div class="text-lg font-bold text-blue-600">{{ $project->tasks_count ?? 0 }}</div>
                                    <div class="text-xs text-blue-600">総タスク数</div>
                                </div>
                                <div class="bg-green-50 p-3 rounded-lg border border-green-200">
                                    <div class="text-lg font-bold text-green-600">{{ $project->completed_tasks_count ?? 0 }}</div>
                                    <div class="text-xs text-green-600">完了タスク</div>
                                </div>
                            </div>

                            {{-- 進捗バー --}}
                            <div class="mb-4">
                                @php
                                    $totalTasks = $project->tasks_count ?? 0;
                                    $completedTasks = $project->completed_tasks_count ?? 0;
                                    $progressPercentage = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 1) : 0;
                                @endphp
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-sm font-medium text-gray-700">プロジェクト進捗</span>
                                    <span class="text-sm font-bold text-gray-800">{{ $progressPercentage }}%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" style="width: {{ $progressPercentage }}%"></div>
                                </div>
                            </div>

                            {{-- アクションボタン --}}
                            <div class="flex gap-2">
                                {{-- WBS/ガントチャートへのリンク --}}
                                <a href="{{ route('tasks.index', $project) }}"
                                   class="flex-1 px-4 py-3 bg-blue-500 hover:bg-blue-600 text-white text-center text-sm font-bold rounded-lg transition shadow-sm hover:shadow-md">
                                    📊 WBS/ガント
                                </a>

                                {{-- プロジェクト編集 --}}
                                <a href="{{ route('projects.edit', $project) }}"
                                   class="px-4 py-3 bg-gray-500 hover:bg-gray-600 text-white text-sm font-bold rounded-lg transition shadow-sm hover:shadow-md">
                                    ✏️ 編集
                                </a>

                                {{-- プロジェクト削除 --}}
                                <form action="{{ route('projects.destroy', $project) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            onclick="return confirm('このプロジェクトを削除しますか？関連するタスクも全て削除されます。')"
                                            class="px-4 py-3 bg-red-500 hover:bg-red-600 text-white text-sm font-bold rounded-lg transition shadow-sm hover:shadow-md">
                                        🗑️
                                    </button>
                                </form>
                            </div>

                            {{-- 作成者・作成日 --}}
                            <div class="mt-4 pt-3 border-t border-gray-200 text-xs text-gray-500">
                                <div>作成者: {{ $project->createdBy->name ?? '不明' }}</div>
                                <div>作成日: {{ $project->created_at->format('Y/m/d') }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- ページネーション --}}
                @if($projects->hasPages())
                    <div class="mt-8">
                        {{ $projects->links() }}
                    </div>
                @endif

            @else
                {{-- プロジェクトが無い場合 --}}
                <div class="text-center py-12">
                    <div class="text-6xl mb-4">📁</div>
                    <h3 class="text-xl font-bold text-gray-600 mb-2">プロジェクトがありません</h3>
                    <p class="text-gray-500 mb-6">最初のプロジェクトを作成して、タスク管理を始めましょう。</p>
                    <a href="{{ route('projects.create') }}"
                       class="inline-block px-8 py-4 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-bold transition shadow-md">
                        ➕ 最初のプロジェクトを作成
                    </a>
                </div>
            @endif
        </div>
    </div>
</x-portal-layout>
