<x-portal-layout>
    <div class="flex flex-col h-full bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden">

        <div class="bg-slate-100 border-b-2 border-gray-200 p-6 flex-shrink-0">
            <div class="flex justify-between items-start">

                {{-- 左側：タイトルと説明 (幅 2/3) --}}
                <div class="w-2/3">
                    <h1 class="text-3xl font-bold text-gray-800">
                        📊 {{ $project->name }} - WBS/ガントチャート
                    </h1>
                    <p class="text-gray-600 mt-1">{{ $project->description }}</p>
                </div>

                {{-- 右側：ボタン群 (幅 1/3) --}}
                <div class="w-1/3 flex justify-end gap-3">
                    {{-- 表示切り替えボタン --}}
                    <div class="bg-white rounded-lg border border-gray-300 p-1 flex items-center">
                        {{-- WBSボタン：最初からアクティブなクラスを追加 --}}
                        <button id="wbs-view-btn"
                            class="bg-blue-500 text-white px-4 py-2 rounded-md transition text-sm font-bold flex flex-col items-center justify-center h-full">
                            <svg class="w-5 h-5 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                            </svg>
                            WBS表示
                        </button>

                        {{-- カンバンボタン --}}
                        <button id="kanban-view-btn"
                            class="bg-transparent text-gray-600 hover:bg-gray-100 px-4 py-2 rounded-md transition text-sm font-bold flex flex-col items-center justify-center h-full">
                            <svg class="w-5 h-5 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2">
                                </path>
                            </svg>
                            カンバン表示
                        </button>

                        {{-- ガントボタン：最初から非アクティブなクラスを追加 --}}
                        <button id="gantt-view-btn"
                            class="bg-transparent text-gray-600 hover:bg-gray-100 px-4 py-2 rounded-md transition text-sm font-bold flex flex-col items-center justify-center h-full">
                            <svg class="w-5 h-5 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 8v8m-8-8v8m-4-4h16"></path>
                            </svg>
                            ガント表示
                        </button>
                    </div>

                    {{-- 新規タスク作成ボタン --}}
                    <button id="add-task-btn"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-bold transition shadow-md flex flex-col items-center justify-center text-sm h-full">
                        <span class="text-2xl font-normal">➕</span>
                        <span class="mt-1">新規タスク</span>
                    </button>
                </div>
            </div>
        </div>

        {{--  メインのコンテンツエリア。残りの高さを全て使い、内部でスクロールさせる --}}
        <div class="flex-grow p-6 overflow-y-auto flex flex-col">

            {{-- フラッシュメッセージ --}}
            <x-flash-message />

            {{-- プロジェクト進捗サマリー (高さは固定) --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8 flex-shrink-0">
                <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                    <div class="text-2xl font-bold text-blue-600" id="total-tasks">
                        {{ $parentTasks->sum(function ($task) {return 1 + $task->children->count();}) }}</div>
                    <div class="text-sm text-blue-600">総タスク数</div>
                </div>
                <div class="bg-green-50 p-4 rounded-lg border border-green-200">
                    <div class="text-2xl font-bold text-green-600" id="completed-tasks">
                        {{ $parentTasks->sum(function ($task) {
                            return ($task->status === 'done' ? 1 : 0) + $task->children->where('status', 'done')->count();
                        }) }}
                    </div>
                    <div class="text-sm text-green-600">完了タスク</div>
                </div>
                <div class="bg-yellow-50 p-4 rounded-lg border border-yellow-200">
                    <div class="text-2xl font-bold text-yellow-600" id="in-progress-tasks">
                        {{ $parentTasks->sum(function ($task) {
                            return ($task->status === 'in_progress' ? 1 : 0) + $task->children->where('status', 'in_progress')->count();
                        }) }}
                    </div>
                    <div class="text-sm text-yellow-600">進行中タスク</div>
                </div>
                <div class="bg-purple-50 p-4 rounded-lg border border-purple-200">
                    <div class="text-2xl font-bold text-purple-600" id="progress-percentage">
                        {{ $parentTasks->sum(function ($task) {
                            return 1 + $task->children->count();
                        }) > 0
                            ? round(
                                ($parentTasks->sum(function ($task) {
                                    return ($task->status === 'done' ? 1 : 0) + $task->children->where('status', 'done')->count();
                                }) /
                                    $parentTasks->sum(function ($task) {
                                        return 1 + $task->children->count();
                                    })) *
                                    100,
                                1,
                            )
                            : 0 }}%
                    </div>
                    <div class="text-sm text-purple-600">全体進捗</div>
                </div>
            </div>

            {{--  WBSとガントチャートのラッパー。残りの高さを全て使う --}}
            <div id="wbs-gantt-wrapper" class="flex-grow flex flex-col min-h-0">

                {{-- WBS表示エリア --}}
                <div id="wbs-container" class="bg-white flex-grow overflow-auto min-h-[400px]">
                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse border border-gray-300">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="border border-gray-300 px-4 py-3 text-left font-bold">WBS</th>
                                    <th class="border border-gray-300 px-4 py-3 text-left font-bold">タスク名</th>
                                    <th class="border border-gray-300 px-4 py-3 text-left font-bold">担当者</th>
                                    <th class="border border-gray-300 px-4 py-3 text-left font-bold">ステータス</th>
                                    <th class="border border-gray-300 px-4 py-3 text-left font-bold">開始予定</th>
                                    <th class="border border-gray-300 px-4 py-3 text-left font-bold">終了予定</th>
                                    <th class="border border-gray-300 px-4 py-3 text-left font-bold">開始日</th>
                                    <th class="border border-gray-300 px-4 py-3 text-left font-bold">終了日</th>
                                    <th class="border border-gray-300 px-4 py-3 text-left font-bold">進捗</th>
                                    <th class="border border-gray-300 px-4 py-3 text-left font-bold">工数</th>
                                    <th class="border border-gray-300 px-4 py-3 text-left font-bold">操作</th>
                                </tr>
                            </thead>
                            <tbody id="tasks-tbody">
                                @foreach ($parentTasks as $parentTask)
                                    {{-- 親タスク行 --}}
                                    <tr class="hover:bg-gray-50 parent-task" data-task-id="{{ $parentTask->id }}">
                                        <td class="border border-gray-300 px-4 py-3 font-bold">
                                            {{ $parentTask->wbs_number }}
                                        </td>
                                        <td class="border border-gray-300 px-4 py-3">
                                            <div class="flex items-center">
                                                @if ($parentTask->children->count() > 0)
                                                    <button class="expand-btn mr-2 text-gray-500 hover:text-gray-700"
                                                        data-task-id="{{ $parentTask->id }}">
                                                        <span class="expand-icon">▶</span>
                                                    </button>
                                                @endif
                                                <span class="font-bold text-blue-800">{{ $parentTask->title }}</span>
                                            </div>
                                        </td>
                                        <td class="border border-gray-300 px-4 py-3">
                                            {{ $parentTask->user ? $parentTask->user->name : '未割り当て' }}
                                        </td>
                                        <td class="border border-gray-300 px-4 py-3">
                                            <span
                                                class="px-2 py-1 rounded-full text-xs font-medium {{ $parentTask->status_class }}">
                                                {{ $parentTask->status_label }}
                                            </span>
                                        </td>
                                        <td class="border border-gray-300 px-4 py-3">
                                            {{ $parentTask->planned_start_date?->format('Y/m/d') ?? '-' }}
                                        </td>
                                        <td class="border border-gray-300 px-4 py-3">
                                            {{ $parentTask->planned_end_date?->format('Y/m/d') ?? '-' }}
                                        </td>
                                        <td class="border border-gray-300 px-4 py-3">
                                            {{ $parentTask->actual_start_date?->format('Y/m/d') ?? '-' }}
                                        </td>
                                        <td class="border border-gray-300 px-4 py-3">
                                            {{ $parentTask->actual_end_date?->format('Y/m/d') ?? '-' }}
                                        </td>
                                        <td class="border border-gray-300 px-4 py-3">
                                            <div class="w-full bg-gray-200 rounded-full h-2">
                                                <div class="bg-blue-600 h-2 rounded-full"
                                                    style="width: {{ $parentTask->progress_percentage }}%"></div>
                                            </div>
                                            <span
                                                class="text-xs text-gray-600">{{ $parentTask->progress_percentage }}%</span>
                                        </td>
                                        <td class="border border-gray-300 px-4 py-3 text-sm">
                                            <div>予定: {{ $parentTask->planned_effort ?? '-' }}h</div>
                                            <div>実績: {{ $parentTask->actual_effort ?? '-' }}h</div>
                                        </td>
                                        <td class="border border-gray-300 px-4 py-3">
                                            <div class="flex gap-1">
                                                <button
                                                    class="edit-task-btn px-2 py-1 bg-blue-500 hover:bg-blue-600 text-white text-xs rounded"
                                                    data-task-id="{{ $parentTask->id }}">
                                                    編集
                                                </button>
                                                <button
                                                    class="add-child-btn px-2 py-1 bg-green-500 hover:bg-green-600 text-white text-xs rounded"
                                                    data-parent-id="{{ $parentTask->id }}">
                                                    子タスクを追加
                                                </button>
                                            </div>
                                        </td>
                                    </tr>

                                    {{-- 子タスク行（初期は非表示） --}}
                                    @foreach ($parentTask->children as $childTask)
                                        <tr class="hover:bg-gray-50 child-task hidden"
                                            data-parent-id="{{ $parentTask->id }}"
                                            data-task-id="{{ $childTask->id }}">
                                            <td class="border border-gray-300 px-4 py-3 pl-8">
                                                {{ $childTask->wbs_number }}
                                            </td>
                                            <td class="border border-gray-300 px-4 py-3 pl-8">
                                                <span class="text-gray-700">{{ $childTask->title }}</span>
                                            </td>
                                            <td class="border border-gray-300 px-4 py-3">
                                                {{ $childTask->user ? $childTask->user->name : '未割り当て' }}
                                            </td>
                                            <td class="border border-gray-300 px-4 py-3">
                                                <span
                                                    class="px-2 py-1 rounded-full text-xs font-medium {{ $childTask->status_class }}">
                                                    {{ $childTask->status_label }}
                                                </span>
                                            </td>
                                            <td class="border border-gray-300 px-4 py-3">
                                                {{ $childTask->planned_start_date?->format('Y/m/d') ?? '-' }}
                                            </td>
                                            <td class="border border-gray-300 px-4 py-3">
                                                {{ $childTask->planned_end_date?->format('Y/m/d') ?? '-' }}
                                            </td>
                                            <td class="border border-gray-300 px-4 py-3">
                                                {{ $childTask->actual_start_date?->format('Y/m/d') ?? '-' }}
                                            </td>
                                            <td class="border border-gray-300 px-4 py-3">
                                                {{ $childTask->actual_end_date?->format('Y/m/d') ?? '-' }}
                                            </td>
                                            <td class="border border-gray-300 px-4 py-3">
                                                <div class="w-full bg-gray-200 rounded-full h-2">
                                                    <div class="bg-green-600 h-2 rounded-full"
                                                        style="width: {{ $childTask->progress_percentage }}%"></div>
                                                </div>
                                                <span
                                                    class="text-xs text-gray-600">{{ $childTask->progress_percentage }}%</span>
                                            </td>
                                            <td class="border border-gray-300 px-4 py-3 text-sm">
                                                <div>予定: {{ $childTask->planned_effort ?? '-' }}h</div>
                                                <div>実績: {{ $childTask->actual_effort ?? '-' }}h</div>
                                            </td>
                                            <td class="border border-gray-300 px-4 py-3">
                                                <div class="flex gap-1">
                                                    <button
                                                        class="edit-task-btn px-2 py-1 bg-blue-500 hover:bg-blue-600 text-white text-xs rounded"
                                                        data-task-id="{{ $childTask->id }}">
                                                        編集
                                                    </button>
                                                    <button
                                                        class="delete-task-btn px-2 py-1 bg-red-500 hover:bg-red-600 text-white text-xs rounded"
                                                        data-task-id="{{ $childTask->id }}">
                                                        削除
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{--  カンバン表示エリア --}}
                <div id="kanban-container" class="bg-gray-100 flex-grow p-4 overflow-x-auto min-h-[400px]"
                    style="display: none;">
                    {{-- カンバンボード本体 (横スクロール) --}}
                    <div class="flex gap-4 h-full">

                        {{-- ToDo (未着手) カラム --}}
                        <div class="flex-1 bg-gray-200 rounded-lg shadow-md flex flex-col">
                            <div class="p-3 bg-gray-300 rounded-t-lg flex-shrink-0">
                                <h3 class="font-bold text-gray-800">📝 ToDo (未着手)</h3>
                            </div>
                            {{-- ▼▼▼ data-status="todo" を追加 ▼▼▼ --}}
                            <div class="p-2 space-y-2 overflow-y-auto flex-grow" data-status="todo">
                                <div class="p-4 text-center text-gray-500">（タスクはありません）</div>
                            </div>
                        </div>

                        {{-- In Progress (進行中) カラム --}}
                        <div class="flex-1 bg-gray-200 rounded-lg shadow-md flex flex-col">
                            <div class="p-3 bg-yellow-300 rounded-t-lg flex-shrink-0">
                                <h3 class="font-bold text-yellow-800">🏃 In Progress (進行中)</h3>
                            </div>
                            {{-- ▼▼▼ data-status="in_progress" を追加 ▼▼▼ --}}
                            <div class="p-2 space-y-2 overflow-y-auto flex-grow" data-status="in_progress">
                                {{-- JSで追加される --}}
                            </div>
                        </div>

                        {{-- Done (完了) カラム --}}
                        <div class="flex-1 bg-gray-200 rounded-lg shadow-md flex flex-col">
                            <div class="p-3 bg-green-300 rounded-t-lg flex-shrink-0">
                                <h3 class="font-bold text-green-800">✅ Done (完了)</h3>
                            </div>
                            {{-- ▼▼▼ data-status="done" を追加 ▼▼▼ --}}
                            <div class="p-2 space-y-2 overflow-y-auto flex-grow" data-status="done">
                                {{-- JSで追加される --}}
                            </div>
                        </div>

                    </div>
                </div>

                {{-- ガントチャート表示エリア（初期は非表示） --}}
                <div id="gantt-container" class="bg-white flex-grow relative min-h-[400px]" style="display: none;">

                    <div
                        class="mb-4 flex justify-between items-center flex-shrink-0 absolute top-0 left-0 right-0 p-4 z-10 bg-white bg-opacity-75 backdrop-blur-sm">
                        <h3 class="text-lg font-bold">ガントチャート</h3>
                        <div class="flex gap-2">
                            <button id="gantt-zoom-out"
                                class="px-3 py-1 bg-gray-500 hover:bg-gray-600 text-white text-sm rounded">縮小</button>
                            <button id="gantt-zoom-in"
                                class="px-3 py-1 bg-gray-500 hover:bg-gray-600 text-white text-sm rounded">拡大</button>
                        </div>
                    </div>

                    {{-- ガントチャート本体。高さを確保するため、calc使用 --}}
                    <div id="gantt-chart" class="w-full h-full pt-16" style="min-height: calc(100% - 4rem);"></div>
                </div>
            </div>

        </div>
    </div>

    {{-- タスク作成・編集モーダル --}}
    <div id="task-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-2xl shadow-xl max-w-2xl w-full max-h-90vh overflow-y-auto">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 id="modal-title" class="text-2xl font-bold">新規タスク作成</h2>
                        <button id="close-modal" class="text-gray-500 hover:text-gray-700 text-2xl">×</button>
                    </div>

                    <form id="task-form">
                        <input type="hidden" id="task-id" name="task_id">
                        <input type="hidden" id="parent-id" name="parent_id">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {{-- タスク名 --}}
                            <div class="md:col-span-2">
                                <label for="title" class="block text-sm font-bold text-gray-700 mb-2">タスク名
                                    *</label>
                                <input type="text" id="title" name="title" required
                                    class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            {{-- 説明 --}}
                            <div class="md:col-span-2">
                                <label for="description" class="block text-sm font-bold text-gray-700 mb-2">説明</label>
                                <textarea id="description" name="description" rows="3"
                                    class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                            </div>

                            {{-- 担当者 --}}
                            <div>
                                <label for="user_id" class="block text-sm font-bold text-gray-700 mb-2">担当者</label>
                                <select id="user_id" name="user_id"
                                    class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">未割り当て</option>
                                    @foreach ($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- ステータス --}}
                            <div>
                                <label for="status" class="block text-sm font-bold text-gray-700 mb-2">ステータス</label>
                                <select id="status" name="status"
                                    class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="todo">未着手</option>
                                    <option value="in_progress">進行中</option>
                                    <option value="done">完了</option>
                                </select>
                            </div>

                            {{-- 開始予定日 --}}
                            <div>
                                <label for="planned_start_date"
                                    class="block text-sm font-bold text-gray-700 mb-2">開始予定日</label>
                                <input type="date" id="planned_start_date" name="planned_start_date"
                                    class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            {{-- 終了予定日 --}}
                            <div>
                                <label for="planned_end_date"
                                    class="block text-sm font-bold text-gray-700 mb-2">終了予定日</label>
                                <input type="date" id="planned_end_date" name="planned_end_date"
                                    class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>


                            {{-- 予定工数 --}}
                            <div>
                                <label for="planned_effort"
                                    class="block text-sm font-bold text-gray-700 mb-2">予定工数（時間）</label>
                                <input type="number" id="planned_effort" name="planned_effort" step="0.5"
                                    min="0"
                                    class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            {{-- 実際工数（編集時のみ表示） --}}
                            <div id="actual-effort-container" class="hidden">
                                <label for="actual_effort"
                                    class="block text-sm font-bold text-gray-700 mb-2">実際工数（時間）</label>
                                <input type="number" id="actual_effort" name="actual_effort" step="0.5"
                                    min="0"
                                    class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            {{-- 開始日（実績） --}}
                            <div id="actual-start-date-container" class="hidden">
                                <label for="actual_start_date"
                                    class="block text-sm font-bold text-gray-700 mb-2">開始日</label>
                                <input type="date" id="actual_start_date" name="actual_start_date"
                                    class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            {{-- 終了日（実績） --}}
                            <div id="actual-end-date-container" class="hidden">
                                <label for="actual_end_date"
                                    class="block text-sm font-bold text-gray-700 mb-2">終了日</label>
                                <input type="date" id="actual_end_date" name="actual_end_date"
                                    class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>

                        <div class="flex justify-end gap-3 mt-6">
                            <button type="button" id="cancel-btn"
                                class="px-6 py-3 bg-gray-300 hover:bg-gray-400 rounded-lg font-bold">
                                キャンセル
                            </button>
                            <button type="submit" id="save-btn"
                                class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-bold">
                                保存
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            (function() { // --- 即時実行関数で全体を囲み、グローバルスコープの汚染を防ぐ ---

                // --- グローバル変数 ---
                const projectId = {{ $project->id }};
                let ganttInstance = null; // ガントチャートのインスタンス
                let kanbanInitialized = false; // カンバンボードが初期化済みかどうかのフラグ

                // --- 初期化処理 ---
                document.addEventListener('DOMContentLoaded', function() {
                    if (typeof gantt === 'undefined') {
                        console.error('dhtmlx-gantt is not loaded!');
                        const ganttContainer = document.getElementById('gantt-container');
                        if (ganttContainer) {
                            ganttContainer.innerHTML =
                                '<div class="p-8 text-center text-red-500 font-bold">エラー: ガントチャートライブラリの読み込みに失敗しました。</div>';
                        }
                        return;
                    }
                    initializeEventListeners();
                });

                // --- イベントリスナーの登録 ---
                function initializeEventListeners() {
                    // 表示切り替えボタン
                    document.getElementById('wbs-view-btn').addEventListener('click', () => switchView('wbs'));
                    document.getElementById('kanban-view-btn').addEventListener('click', () => switchView('kanban'));
                    document.getElementById('gantt-view-btn').addEventListener('click', () => switchView('gantt'));

                    // 新規タスク作成ボタン
                    document.getElementById('add-task-btn').addEventListener('click', () => openTaskModal());

                    // モーダル関連のボタン
                    document.getElementById('close-modal').addEventListener('click', closeTaskModal);
                    document.getElementById('cancel-btn').addEventListener('click', closeTaskModal);
                    document.getElementById('task-form').addEventListener('submit', handleTaskSubmit);

                    // イベント委任 (WBSテーブル内の動的なボタンに対応)
                    const tbody = document.getElementById('tasks-tbody');
                    tbody.addEventListener('click', function(e) {
                        const editBtn = e.target.closest('.edit-task-btn');
                        if (editBtn) {
                            editTask(editBtn.dataset.taskId);
                            return;
                        }
                        const addChildBtn = e.target.closest('.add-child-btn');
                        if (addChildBtn) {
                            openTaskModal(null, addChildBtn.dataset.parentId);
                            return;
                        }
                        const deleteBtn = e.target.closest('.delete-task-btn');
                        if (deleteBtn) {
                            deleteTask(deleteBtn.dataset.taskId);
                            return;
                        }
                        const expandBtn = e.target.closest('.expand-btn');
                        if (expandBtn) {
                            toggleTaskExpansion(expandBtn);
                            return;
                        }
                    });

                    // ガントチャートのズームボタン
                    document.getElementById('gantt-zoom-in').addEventListener('click', () => ganttInstance?.ext.zoom
                    .zoomIn());
                    document.getElementById('gantt-zoom-out').addEventListener('click', () => ganttInstance?.ext.zoom
                        .zoomOut());
                }

                /**
                 * ==================================
                 * 表示モードの切り替え
                 * ==================================
                 */
                function switchView(view) {
                    const wbsContainer = document.getElementById('wbs-container');
                    const kanbanContainer = document.getElementById('kanban-container');
                    const ganttContainer = document.getElementById('gantt-container');
                    const wbsBtn = document.getElementById('wbs-view-btn');
                    const kanbanBtn = document.getElementById('kanban-view-btn');
                    const ganttBtn = document.getElementById('gantt-view-btn');

                    const isWbs = view === 'wbs';
                    const isKanban = view === 'kanban';
                    const isGantt = view === 'gantt';

                    const activeClasses = 'bg-blue-500 text-white';
                    const inactiveClasses = 'bg-transparent text-gray-600 hover:bg-gray-100';
                    const baseClasses =
                        'px-4 py-2 rounded-md transition text-sm font-bold flex flex-col items-center justify-center h-full';

                    wbsContainer.style.display = isWbs ? 'block' : 'none';
                    kanbanContainer.style.display = isKanban ? 'block' : 'none';
                    ganttContainer.style.display = isGantt ? 'block' : 'none';

                    wbsBtn.className = `${baseClasses} ${isWbs ? activeClasses : inactiveClasses}`;
                    kanbanBtn.className = `${baseClasses} ${isKanban ? activeClasses : inactiveClasses}`;
                    ganttBtn.className = `${baseClasses} ${isGantt ? activeClasses : inactiveClasses}`;

                    if (isKanban) {
                        initializeKanbanBoard();
                        renderKanbanTasks();
                    }

                    if (isGantt) {
                        if (!ganttInstance) {
                            setTimeout(() => renderGanttChart(), 150);
                        } else {
                            ganttInstance.render();
                        }
                    }
                }

                /**
                 * ==================================
                 * カンバンボード関連の関数
                 * ==================================
                 */

                function initializeKanbanBoard() {
                    if (kanbanInitialized) return;

                    const todoColumn = document.querySelector('#kanban-container div[data-status="todo"]');
                    const inProgressColumn = document.querySelector('#kanban-container div[data-status="in_progress"]');
                    const doneColumn = document.querySelector('#kanban-container div[data-status="done"]');

                    const columns = [todoColumn, inProgressColumn, doneColumn];
                    columns.forEach(column => {
                        new window.Sortable(column, {
                            group: 'shared',
                            animation: 150,
                            ghostClass: 'opacity-50',
                            onEnd: function(evt) {
                                const taskId = evt.item.dataset.taskId;
                                const newStatus = evt.to.dataset.status;
                                updateTaskStatus(taskId, newStatus);
                            }
                        });
                    });
                    kanbanInitialized = true;
                }

                async function renderKanbanTasks() {
                    const tasks = await fetchAllTasksForKanban();
                    if (tasks === null) return;

                    const todoColumn = document.querySelector('#kanban-container div[data-status="todo"]');
                    const inProgressColumn = document.querySelector('#kanban-container div[data-status="in_progress"]');
                    const doneColumn = document.querySelector('#kanban-container div[data-status="done"]');

                    todoColumn.innerHTML = '';
                    inProgressColumn.innerHTML = '';
                    doneColumn.innerHTML = '';

                    const todoTasks = tasks.filter(t => t.status === 'todo' || t.status === null);
                    const inProgressTasks = tasks.filter(t => t.status === 'in_progress');
                    const doneTasks = tasks.filter(t => t.status === 'done');

                    if (todoTasks.length > 0) {
                        todoTasks.forEach(task => todoColumn.appendChild(createTaskCard(task)));
                    } else {
                        todoColumn.innerHTML = '<div class="p-4 text-center text-gray-500">（タスクはありません）</div>';
                    }
                    if (inProgressTasks.length > 0) {
                        inProgressTasks.forEach(task => inProgressColumn.appendChild(createTaskCard(task)));
                    }
                    if (doneTasks.length > 0) {
                        doneTasks.forEach(task => doneColumn.appendChild(createTaskCard(task)));
                    }
                }

                async function fetchAllTasksForKanban() {
                    try {
                        const response = await fetch('{{ route('tasks.kanbanData', $project) }}');
                        if (!response.ok) throw new Error(`タスクデータの取得に失敗しました (Status: ${response.status})`);
                        return await response.json();
                    } catch (error) {
                        console.error(error);
                        alert(error.message);
                        return null;
                    }
                }

                function createTaskCard(task) {
                    const card = document.createElement('div');
                    card.className = 'bg-white p-3 rounded-md shadow cursor-pointer border-l-4 border-blue-500';
                    card.dataset.taskId = task.id;

                    let userAvatar = '';
                    if (task.user && task.user.avatar_url) {
                        userAvatar =
                            `<img src="${task.user.avatar_url}" alt="${task.user.name}" class="w-6 h-6 rounded-full ml-auto">`;
                    }

                    let dueDate = '';
                    if (task.planned_end_date) {
                        dueDate = '期限: ' + task.planned_end_date.split('T')[0];
                    }

                    card.innerHTML = `
                        <p class="font-bold text-sm text-gray-800">${task.title}</p>
                        <div class="flex justify-between items-center mt-2">
                            <span class="text-xs text-gray-500">${dueDate}</span>
                            ${userAvatar}
                        </div>
                    `;
                    card.addEventListener('click', () => editTask(task.id));
                    return card;
                }

                async function updateTaskStatus(taskId, newStatus) {
                    try {
                        const response = await fetch(`/projects/${projectId}/tasks/${taskId}`, {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                    'content'),
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({
                                status: newStatus
                            })
                        });
                        if (!response.ok) throw new Error('ステータスの更新に失敗しました');
                        updateSummary();
                    } catch (error) {
                        console.error(error);
                        alert(error.message);
                        location.reload();
                    }
                }

                async function updateSummary() {
                    try {
                        const response = await fetch('{{ route('tasks.summary', $project) }}');
                        const summary = await response.json();
                        document.getElementById('total-tasks').textContent = summary.total_tasks;
                        document.getElementById('completed-tasks').textContent = summary.completed_tasks;
                        document.getElementById('in-progress-tasks').textContent = summary.in_progress_tasks;
                        document.getElementById('progress-percentage').textContent = summary.progress_percentage + '%';
                    } catch (error) {
                        console.error('Failed to update summary:', error);
                    }
                }

                /**
                 * ==================================
                 * WBS関連の関数
                 * ==================================
                 */
                function toggleTaskExpansion(button) {
                    const taskId = button.dataset.taskId;
                    const childRows = document.querySelectorAll(`tr[data-parent-id="${taskId}"]`);
                    const icon = button.querySelector('.expand-icon');

                    childRows.forEach(row => row.classList.toggle('hidden'));
                    icon.textContent = icon.textContent === '▶' ? '▼' : '▶';
                }

                /**
                 * ==================================
                 * モーダル関連の関数 (WBSとカンバンで共通)
                 * ==================================
                 */
                function openTaskModal(taskId = null, parentId = null) {
                    const modal = document.getElementById('task-modal');
                    const title = document.getElementById('modal-title');
                    const form = document.getElementById('task-form');
                    form.reset();
                    document.getElementById('task-id').value = taskId || '';
                    document.getElementById('parent-id').value = parentId || '';

                    const actualFields = [
                        document.getElementById('actual-start-date-container'),
                        document.getElementById('actual-end-date-container'),
                        document.getElementById('actual-effort-container')
                    ];

                    if (taskId) {
                        title.textContent = 'タスク編集';
                        actualFields.forEach(field => field.classList.remove('hidden'));
                        fetchTaskData(taskId);
                    } else {
                        title.textContent = parentId ? '子タスク作成' : '新規タスク作成';
                        actualFields.forEach(field => field.classList.add('hidden'));
                    }
                    modal.classList.remove('hidden');
                }

                function closeTaskModal() {
                    document.getElementById('task-modal').classList.add('hidden');
                }

                async function fetchTaskData(taskId) {
                    try {
                        const response = await fetch(`/projects/${projectId}/tasks/${taskId}`);
                        if (!response.ok) throw new Error('Network response was not ok');
                        const task = await response.json();

                        document.getElementById('title').value = task.title || '';
                        document.getElementById('description').value = task.description || '';
                        document.getElementById('user_id').value = task.user_id || '';
                        document.getElementById('status').value = task.status || 'todo';
                        document.getElementById('planned_start_date').value = task.planned_start_date ? task
                            .planned_start_date.split('T')[0] : '';
                        document.getElementById('planned_end_date').value = task.planned_end_date ? task
                            .planned_end_date.split('T')[0] : '';
                        document.getElementById('actual_start_date').value = task.actual_start_date ? task
                            .actual_start_date.split('T')[0] : '';
                        document.getElementById('actual_end_date').value = task.actual_end_date ? task.actual_end_date
                            .split('T')[0] : '';
                        document.getElementById('planned_effort').value = task.planned_effort || '';
                        document.getElementById('actual_effort').value = task.actual_effort || '';
                    } catch (error) {
                        console.error('タスクデータの取得に失敗しました:', error);
                        alert('タスクデータの取得に失敗しました');
                    }
                }

                async function handleTaskSubmit(e) {
                    e.preventDefault();
                    const formData = new FormData(e.target);
                    const taskId = formData.get('task_id');
                    const isEdit = !!taskId;

                    let data = Object.fromEntries(formData.entries());
                    delete data.task_id;

                    try {
                        const url = isEdit ? `/projects/${projectId}/tasks/${taskId}` : `/projects/${projectId}/tasks`;
                        const method = isEdit ? 'PUT' : 'POST';

                        const response = await fetch(url, {
                            method: method,
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                    'content'),
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(data)
                        });

                        const result = await response.json();

                        if (response.ok && result.success) {
                            closeTaskModal();
                            location.reload(); // 成功したらリロードして全体を更新
                        } else {
                            let errorMessage = result.message || 'エラーが発生しました';
                            if (result.errors) {
                                errorMessage += '\n' + Object.values(result.errors).map(e => e.join('\n')).join('\n');
                            }
                            alert(errorMessage);
                        }
                    } catch (error) {
                        console.error('タスクの保存に失敗しました:', error);
                        alert('タスクの保存に失敗しました');
                    }
                }

                function editTask(taskId) {
                    openTaskModal(taskId);
                }

                async function deleteTask(taskId) {
                    if (!confirm('このタスクを削除しますか？\n子タスクがある場合は削除できません。')) return;

                    try {
                        const response = await fetch(`/projects/${projectId}/tasks/${taskId}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                    'content'),
                                'Accept': 'application/json'
                            }
                        });

                        const result = await response.json();

                        if (response.ok && result.success) {
                            location.reload();
                        } else {
                            alert(result.error || '削除に失敗しました');
                        }
                    } catch (error) {
                        console.error('タスクの削除に失敗しました:', error);
                        alert('タスクの削除に失敗しました');
                    }
                }



                // --- ガントチャート関連の関数 ---

                async function renderGanttChart() {
                    const ganttContainer = document.getElementById('gantt-container');
                    const chartContainer = document.getElementById('gantt-chart');

                    // 複数回の高さ取得試行
                    let containerHeight = 0;
                    let attempts = 0;
                    const maxAttempts = 10;

                    while (containerHeight <= 0 && attempts < maxAttempts) {
                        containerHeight = ganttContainer.clientHeight;

                        if (containerHeight <= 0) {
                            // 50ms待ってから再試行
                            await new Promise(resolve => setTimeout(resolve, 50));
                            attempts++;
                        }
                    }

                    // それでも高さが取得できない場合は、親要素から計算
                    if (containerHeight <= 0) {
                        const parentContainer = ganttContainer.closest('.flex-grow');
                        if (parentContainer) {
                            const parentHeight = parentContainer.clientHeight;
                            const siblingHeights = Array.from(parentContainer.children)
                                .filter(child => child !== ganttContainer)
                                .reduce((sum, child) => sum + child.offsetHeight, 0);

                            containerHeight = Math.max(parentHeight - siblingHeights - 100, 400); // 最低400px
                        } else {
                            containerHeight = 600; // フォールバック値
                        }
                    }

                    console.log('Gantt container height:', containerHeight);

                    // 最低高さを保証
                    containerHeight = Math.max(containerHeight, 400);

                    // ガントチャート本体の高さを設定
                    chartContainer.style.height = `${containerHeight}px`;
                    chartContainer.innerHTML = '<div class="p-4">読み込み中...</div>';

                    const gantt = window.gantt;

                    // --- 1. 基本設定 ---
                    gantt.config.xml_date = "%Y-%m-%d";
                    gantt.config.date_format = "%Y-%m-%d %H:%i";
                    gantt.config.date_grid = "%Y/%m/%d";

                    // --- 2. 日本語化設定 ---
                    gantt.plugins({
                        zoom: true,
                        modal: true
                    });

                    // 標準の日本語ロケールを適用
                    gantt.i18n.setLocale("jp");

                    // 必要に応じて追加・上書き
                    Object.assign(gantt.locale.labels, {
                        new_task: "新しいタスク",
                        dhx_cal_today_button: "今日",
                        day_tab: "日",
                        week_tab: "週",
                        month_tab: "月",
                        new_event: "新規イベント",
                        icon_save: "保存",
                        icon_cancel: "キャンセル",
                        icon_details: "詳細",
                        icon_edit: "編集",
                        icon_delete: "削除",
                        confirm_closing: "変更内容は失われます。よろしいですか？",
                        confirm_deleting: "このタスクを削除しますか？",
                        section_description: "説明",
                        section_time: "期間",
                        section_type: "タイプ",
                        section_text: "タスク名",
                        section_start_date: "開始日",
                        section_end_date: "終了日",
                        section_duration: "期間",
                        section_parent: "親タスク",
                        section_priority: "優先度",
                        section_owner: "担当者",
                        section_progress: "進捗",
                        section_template: "テンプレート",
                        save_button: "保存",
                        cancel_button: "キャンセル",
                        delete_button: "削除"
                    });

                    // --- 3. ライトボックス（モーダル）の設定 ---
                    gantt.config.lightbox.sections = [{
                            name: "description",
                            height: 70,
                            map_to: "text",
                            type: "textarea",
                            focus: true
                        },
                        {
                            name: "owner",
                            height: 60,
                            map_to: "owner",
                            type: "select",
                            options: []
                        },
                        {
                            name: "time",
                            type: "duration",
                            map_to: "auto",
                            time_format: ["%Y", "%m", "%d"]
                        },
                        {
                            name: "priority",
                            height: 60,
                            map_to: "priority",
                            type: "select",
                            options: [{
                                    key: 1,
                                    label: "高"
                                },
                                {
                                    key: 2,
                                    label: "中"
                                },
                                {
                                    key: 3,
                                    label: "低"
                                }
                            ]
                        }
                    ];

                    // ライトボックスのフィールドラベル
                    gantt.locale.labels.section_description = "説明";
                    gantt.locale.labels.section_owner = "担当者";
                    gantt.locale.labels.section_time = "期間";
                    gantt.locale.labels.section_priority = "優先度";

                    // 期間入力の設定
                    gantt.config.duration_unit = "day";
                    gantt.config.duration_step = 1;

                    gantt.templates.task_date = function(date) {
                        return gantt.date.date_to_str("%Y年%m月%d日")(date);
                    };

                    // --- 4. 日付形式の設定 ---
                    gantt.config.date_format = "%Y-%m-%d %H:%i";
                    gantt.templates.lightbox_date_format = gantt.date.date_to_str("%Y-%m-%d");
                    gantt.config.date_grid = "%Y-%m-%d";

                    gantt.templates.date_grid = function(date) {
                        if (!date) return "-";
                        return gantt.date.date_to_str("%Y-%m-%d")(date);
                    };

                    gantt.templates.lightbox_header = function(start, end) {
                        var formatFunc = gantt.date.date_to_str("%Y年%m月%d日");
                        return formatFunc(start) + " - " + formatFunc(end);
                    };

                    // --- 5. カラム設定 ---
                    gantt.config.columns = [{
                            name: "wbs",
                            label: "WBS",
                            tree: true,
                            width: 80,
                            resize: true,
                            template: gantt.getWBSCode
                        },
                        {
                            name: "text",
                            label: "タスク名",
                            width: '*',
                            min_width: 200,
                            resize: true
                        },
                        {
                            name: "start_date",
                            label: "開始日",
                            align: "center",
                            width: 100,
                            resize: true,
                            template: function(task) {
                                if (task.start_date) return gantt.date.date_to_str("%Y-%m-%d")(task.start_date);
                                return "-";
                            }
                        },
                        {
                            name: "duration",
                            label: "期間(日)",
                            align: "center",
                            width: 70,
                            resize: true
                        },
                        {
                            name: "user",
                            label: "担当者",
                            align: "center",
                            width: 100,
                            resize: true,
                            template: function(task) {
                                return task.user || '';
                            }
                        },
                        {
                            name: "add",
                            label: "",
                            width: 44
                        }
                    ];

                    // --- 6. ズーム設定 ---
                    gantt.ext.zoom.init({
                        levels: [{
                                name: "day",
                                scale_height: 27,
                                min_column_width: 80,
                                scales: [{
                                    unit: "day",
                                    step: 1,
                                    format: "%m/%d"
                                }]
                            },
                            {
                                name: "week",
                                scale_height: 50,
                                min_column_width: 50,
                                scales: [{
                                        unit: "week",
                                        step: 1,
                                        format: function(date) {
                                            var dateToStr = gantt.date.date_to_str("%Y-%m-%d");
                                            var endDate = gantt.date.add(gantt.date.add(date, 1,
                                                "week"), -1, "day");
                                            return dateToStr(date) + " ～ " + dateToStr(endDate);
                                        }
                                    },
                                    {
                                        unit: "day",
                                        step: 1,
                                        format: "%d"
                                    }
                                ]
                            },
                            {
                                name: "month",
                                scale_height: 50,
                                min_column_width: 120,
                                scales: [{
                                        unit: "month",
                                        step: 1,
                                        format: "%Y年%m月"
                                    },
                                    {
                                        unit: "week",
                                        step: 1,
                                        format: "第%W週"
                                    }
                                ]
                            }
                        ]
                    });

                    // --- 7. 初期化とイベント設定 ---
                    gantt.init(chartContainer);
                    ganttInstance = gantt;

                    gantt.attachEvent("onGanttReady", function() {
                        gantt.attachEvent("onTaskClick", function(id, e) {
                            if (e.target.closest(".gantt_add")) {
                                openTaskModal(null, id);
                                return false;
                            }
                            return true;
                        });
                    });

                    // データ読み込み
                    try {
                        const response = await fetch("{{ route('tasks.ganttData', $project) }}");
                        if (!response.ok) throw new Error('Failed to fetch gantt data');
                        const data = await response.json();
                        gantt.parse(data);
                    } catch (error) {
                        console.error("Gantt Error:", error);
                        chartContainer.innerHTML = '<div class="p-8 text-center text-red-500">ガントチャートの表示に失敗しました。</div>';
                    }
                }

                /**
                 * ==================================
                 * カンバンボード関連の関数
                 * ==================================
                 */
                function initializeKanbanBoard() {
                    // すでに初期化済みの場合は何もしない
                    if (kanbanInitialized) return;

                    const todoColumn = document.querySelector('#kanban-container .bg-gray-300 + .p-2');
                    const inProgressColumn = document.querySelector('#kanban-container .bg-yellow-300 + .p-2');
                    const doneColumn = document.querySelector('#kanban-container .bg-green-300 + .p-2');

                    const columns = [todoColumn, inProgressColumn, doneColumn];
                    columns.forEach(column => {
                        new window.Sortable(column, {
                            group: 'shared',
                            animation: 150,
                            ghostClass: 'opacity-50',
                            onEnd: function(evt) {
                                const taskId = evt.item.dataset.taskId;
                                const newStatus = findStatusForColumn(evt.to);
                                updateTaskStatus(taskId, newStatus);
                            }
                        });
                    });

                    kanbanInitialized = true; // 初期化フラグを立てる
                    console.log('Kanban board initialized.'); // 初期化されたかログで確認
                }

                async function renderKanbanTasks() {
                    console.log('Start rendering kanban tasks...'); // 実行されたかログで確認

                    const tasks = await fetchAllTasks();
                    if (!tasks) {
                        console.error('Failed to fetch tasks for kanban.');
                        return;
                    }

                    const todoColumn = document.querySelector('#kanban-container .bg-gray-300 + .p-2');
                    const inProgressColumn = document.querySelector('#kanban-container .bg-yellow-300 + .p-2');
                    const doneColumn = document.querySelector('#kanban-container .bg-green-300 + .p-2');

                    // 一旦コンテナを空にする
                    todoColumn.innerHTML = '';
                    inProgressColumn.innerHTML = '';
                    doneColumn.innerHTML = '';

                    // タスクを分類
                    const todoTasks = tasks.filter(t => t.status === 'todo' || t.status === null);
                    const inProgressTasks = tasks.filter(t => t.status === 'in_progress');
                    const doneTasks = tasks.filter(t => t.status === 'done');

                    // 各カラムにタスクを描画
                    if (todoTasks.length > 0) {
                        todoTasks.forEach(task => todoColumn.appendChild(createTaskCard(task)));
                    } else {
                        todoColumn.innerHTML = '<div class="p-4 text-center text-gray-500">（タスクはありません）</div>';
                    }

                    if (inProgressTasks.length > 0) {
                        inProgressTasks.forEach(task => inProgressColumn.appendChild(createTaskCard(task)));
                    }

                    if (doneTasks.length > 0) {
                        doneTasks.forEach(task => doneColumn.appendChild(createTaskCard(task)));
                    }

                    console.log('Finished rendering kanban tasks.');
                }

                // 全タスクを取得するAPIを叩くヘルパー関数
                async function fetchAllTasks() {
                    try {
                        // WBSのデータを流用。もし専用APIを作るならURLを変更。
                        const response = await fetch('{{ route('tasks.kanbanData', $project) }}');
                        if (!response.ok) throw new Error('タスクデータの取得に失敗しました');
                        const data = await response.json();
                        return data;
                    } catch (error) {
                        console.error(error);
                        alert(error.message);
                        return null;
                    }
                }

                // タスクカードのHTMLを生成する関数
                function createTaskCard(task) {
                    const card = document.createElement('div');
                    card.className = 'bg-white p-3 rounded-md shadow cursor-pointer border-l-4 border-blue-500';
                    card.dataset.taskId = task.id; // data属性としてタスクIDを保持

                    let userAvatar = task.user ?
                        `<img src="${task.user.avatar_path ? '/storage/' + task.user.avatar_path : '/images/default-avatar.png'}" class="w-6 h-6 rounded-full ml-auto">` :
                        '';

                    card.innerHTML = `
                        <p class="font-bold text-sm text-gray-800">${task.title}</p>
                        <div class="flex justify-between items-center mt-2">
                            <span class="text-xs text-gray-500">${task.planned_end_date ? '期限: ' + task.planned_end_date : ''}</span>
                            ${userAvatar}
                        </div>
                    `;
                    // カードがクリックされたら、編集モーダルを開く
                    card.addEventListener('click', () => editTask(task.id));
                    return card;
                }

                // ドロップされたカラムからstatusを判定するヘルパー関数
                function findStatusForColumn(columnEl) {
                    if (columnEl.previousElementSibling.classList.contains('bg-yellow-300')) return 'in_progress';
                    if (columnEl.previousElementSibling.classList.contains('bg-green-300')) return 'done';
                    return 'todo';
                }

                // タスクのステータスを更新するAPIを叩く関数
                async function updateTaskStatus(taskId, newStatus) {
                    try {
                        const response = await fetch(`/projects/${projectId}/tasks/${taskId}`, {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                    'content'),
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({
                                status: newStatus
                            })
                        });
                        if (!response.ok) throw new Error('ステータスの更新に失敗しました');

                        // location.reload(); // 成功したらリロード（シンプルだがちらつく）
                        console.log(`Task ${taskId} status updated to ${newStatus}`);

                    } catch (error) {
                        console.error(error);
                        alert(error.message);
                        // エラーが起きたら画面をリロードして元の状態に戻す
                        location.reload();
                    }
                }


            })(); // --- 即時実行関数の終了 ---
        </script>
    @endpush
</x-portal-layout>
