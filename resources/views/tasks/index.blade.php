<x-portal-layout>
    <div class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden">
        {{-- ヘッダー --}}
        <div class="bg-slate-100 border-b-2 border-gray-200 p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">
                        📊 {{ $project->name }} - WBS/ガントチャート
                    </h1>
                    <p class="text-gray-600 mt-1">{{ $project->description }}</p>
                </div>
                <div class="flex gap-3">
                    {{-- 表示切り替えボタン --}}
                    <div class="bg-white rounded-lg border border-gray-300 p-1">
                        <button id="wbs-view-btn" class="px-4 py-2 rounded-md bg-blue-500 text-white transition">
                            📋 WBS表示
                        </button>
                        <button id="gantt-view-btn" class="px-4 py-2 rounded-md hover:bg-gray-100 transition">
                            📈 ガント表示
                        </button>
                    </div>
                    {{-- 新規タスク作成ボタン --}}
                    <button id="add-task-btn"
                        class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-bold transition shadow-md">
                        ➕ 新規タスク
                    </button>
                </div>
            </div>
        </div>

        <div class="p-6">
            {{-- フラッシュメッセージ --}}
            <x-flash-message />

            {{-- プロジェクト進捗サマリー --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
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

            {{-- WBS表示エリア --}}
            <div id="wbs-container" class="bg-white">
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
                                    <td class="border border-gray-300 px-4 py-3 font-bold">{{ $parentTask->wbs_number }}
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
                                        data-parent-id="{{ $parentTask->id }}" data-task-id="{{ $childTask->id }}">
                                        <td class="border border-gray-300 px-4 py-3 pl-8">{{ $childTask->wbs_number }}
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

            {{-- ガントチャート表示エリア（初期は非表示） --}}
            <div id="gantt-container" class="bg-white hidden">
                <div class="mb-4 flex justify-between items-center">
                    <h3 class="text-lg font-bold">ガントチャート</h3>
                    <div class="flex gap-2">
                        <button id="gantt-zoom-out"
                            class="px-3 py-1 bg-gray-500 hover:bg-gray-600 text-white text-sm rounded">
                            縮小
                        </button>
                        <button id="gantt-zoom-in"
                            class="px-3 py-1 bg-gray-500 hover:bg-gray-600 text-white text-sm rounded">
                            拡大
                        </button>
                    </div>
                </div>
                <div id="gantt-chart" class="overflow-x-auto border border-gray-300 rounded-lg">
                    {{-- ガントチャートはJavaScriptで動的生成 --}}
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
            (function() { // --- [追加] 即時実行関数で全体を囲む ---
                // グローバル変数
                const projectId = {{ $project->id }};
                let ganttInstance = null; // ガントのインスタンスを保持する変数

                // ページの読み込みが完了したら、全ての処理を開始
                document.addEventListener('DOMContentLoaded', function() {
                    // ganttオブジェクトが存在しなかったら、処理を中断
                    if (typeof gantt === 'undefined') {
                        console.error('dhtmlx-gantt is not loaded!');
                        return;
                    }
                    initializeEventListeners();
                });

                // --- イベントリスナーの初期化 ---
                function initializeEventListeners() {
                    // 表示切り替え
                    document.getElementById('wbs-view-btn').addEventListener('click', () => switchView('wbs'));
                    document.getElementById('gantt-view-btn').addEventListener('click', () => switchView('gantt'));

                    // 新規タスク作成
                    document.getElementById('add-task-btn').addEventListener('click', () => openTaskModal());

                    // モーダル操作
                    document.getElementById('close-modal').addEventListener('click', closeTaskModal);
                    document.getElementById('cancel-btn').addEventListener('click', closeTaskModal);
                    document.getElementById('task-form').addEventListener('submit', handleTaskSubmit);

                    // イベント委任 (WBSテーブル)
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

                // --- WBSの展開・折りたたみ ---
                function toggleTaskExpansion(button) {
                    const taskId = button.dataset.taskId;
                    const childRows = document.querySelectorAll(`tr[data-parent-id="${taskId}"]`);
                    const icon = button.querySelector('.expand-icon');
                    const isCollapsed = icon.textContent === '▶';

                    childRows.forEach(row => row.classList.toggle('hidden', !isCollapsed));
                    icon.textContent = isCollapsed ? '▼' : '▶';
                }

                // --- 表示切り替え ---
                function switchView(view) {
                    const wbsContainer = document.getElementById('wbs-container');
                    const ganttContainer = document.getElementById('gantt-container');
                    const wbsBtn = document.getElementById('wbs-view-btn');
                    const ganttBtn = document.getElementById('gantt-view-btn');

                    if (view === 'wbs') {
                        wbsContainer.classList.remove('hidden');
                        ganttContainer.classList.add('hidden');
                        wbsBtn.classList.add('bg-blue-500', 'text-white');
                        wbsBtn.classList.remove('hover:bg-gray-100');
                        ganttBtn.classList.remove('bg-blue-500', 'text-white');
                        ganttBtn.classList.add('hover:bg-gray-100');
                    } else {
                        wbsContainer.classList.add('hidden');
                        ganttContainer.classList.remove('hidden');
                        ganttBtn.classList.add('bg-blue-500', 'text-white');
                        ganttBtn.classList.remove('hover:bg-gray-100');
                        wbsBtn.classList.remove('bg-blue-500', 'text-white');
                        wbsBtn.classList.add('hover:bg-gray-100');

                        // ガントチャートがまだ初期化されていなければ、描画する
                        if (!ganttInstance) {
                            renderGanttChart();
                        }
                    }
                }

                // --- モーダル関連の関数 ---
                // (openTaskModal, closeTaskModal, fetchTaskData, handleTaskSubmit, editTask, deleteTask は、
                //  前回の修正版とほぼ同じなので、ここでは省略します。後で完全版を記載します)

                // --- ガントチャート描画 ---
                async function renderGanttChart() {
                    const container = document.getElementById('gantt-chart');
                    container.innerHTML = '<div class="p-4">読み込み中...</div>'; // ローディング表示

                    // [修正] ganttオブジェクトをローカル変数として作成
                    const gantt = window.gantt;

                    // --- 設定 ---
                    gantt.config.xml_date = "%Y-%m-%d";
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
                            resize: true
                        },
                        {
                            name: "duration",
                            label: "期間",
                            align: "center",
                            width: 60,
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
                    gantt.config.scales = [{
                            unit: "month",
                            step: 1,
                            format: "%Y年 %F"
                        },
                        {
                            unit: "day",
                            step: 1,
                            format: "%j"
                        }
                    ];
                    gantt.i18n.setLocale("jp"); // 日本語化
                    gantt.plugins({
                        zoom: true
                    }); // ズームプラグイン有効化

                    gantt.init(container);
                    ganttInstance = gantt; // [追加] グローバル変数にインスタンスを保持

                    // --- イベントリスナー ---
                    gantt.attachEvent("onTaskClick", function(id, e) {
                        if (e.target.closest(".gantt_add")) {
                            openTaskModal(null, id);
                            return false;
                        }
                        return true;
                    });

                    // --- データ読み込み ---
                    try {
                        const response = await fetch("{{ route('tasks.ganttData', $project) }}");
                        if (!response.ok) throw new Error('Failed to fetch gantt data');
                        const data = await response.json();
                        gantt.parse(data); // `data`キーでラップされていることを想定
                    } catch (error) {
                        console.error("Gantt Error:", error);
                        container.innerHTML = '<div class="p-8 text-center text-red-500">ガントチャートの表示に失敗しました。</div>';
                    }
                }

                // --- ここに、省略したモーダル関連の関数をペースト ---
                function openTaskModal(taskId = null, parentId = null) {
                    /* ... */ }

                function closeTaskModal() {
                    /* ... */ }
                async function fetchTaskData(taskId) {
                    /* ... */ }
                async function handleTaskSubmit(e) {
                    /* ... */ }

                function editTask(taskId) {
                    /* ... */ }
                async function deleteTask(taskId) {
                    /* ... */ }
                // ---------------------------------------------

            })(); // --- [追加] 即時実行関数の終了 ---
        </script>
    @endpush
</x-portal-layout>
