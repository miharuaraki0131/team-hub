<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Events\TaskAssigned;

class TaskController extends Controller
{
    /**
     * プロジェクトのWBS/ガントチャート表示
     * ここがメイン機能：プロジェクトに属する全タスクを階層構造で表示
     */
    public function index(Project $project)
    {
        // プロジェクトに属するタスクを階層構造で取得
        // parent_id が null の親タスクと、その子タスクを取得
        $parentTasks = Task::where('project_id', $project->id)
            ->whereNull('parent_id')
            ->with(['children.user', 'user']) // 子タスクとユーザー情報も同時取得
            ->orderBy('position')
            ->get();

        // 担当可能なユーザー一覧も取得（タスク作成・編集で使用）
        $users = User::all();

        return view('tasks.index', compact('project', 'parentTasks', 'users'));
    }

    /**
     * 新しいタスクを作成
     * JSON API形式で、リアルタイムにタスクを追加できるようにする
     */
    public function store(Request $request, Project $project)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'user_id' => 'nullable|exists:users,id',
            'parent_id' => 'nullable|exists:tasks,id',
            'planned_start_date' => 'nullable|date',
            'planned_end_date' => 'nullable|date|after_or_equal:planned_start_date',
            'planned_effort' => 'nullable|numeric|min:0',
            'status' => 'in:todo,in_progress,done',
        ]);

        // 親タスクが指定されている場合、同一プロジェクト内かチェック
        if (isset($validated['parent_id']) && $validated['parent_id']) {
            $parentTask = Task::find($validated['parent_id']);
            if ($parentTask->project_id !== $project->id) {
                return response()->json(['error' => '親タスクは同一プロジェクト内である必要があります'], 422);
            }
        }

        $task = Task::create([
            'project_id' => $project->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'user_id' => $validated['user_id'] ?? null,
            'parent_id' => $validated['parent_id'] ?? null,
            'planned_start_date' => $validated['planned_start_date'] ?? null,
            'planned_end_date' => $validated['planned_end_date'] ?? null,
            'planned_effort' => $validated['planned_effort'] ?? null,
            'status' => $validated['status'] ?? 'todo',
            'created_by' => Auth::id(),
        ]);

        // ★★★ タスク保存後、イベントを発行！ ★★★
        TaskAssigned::dispatch($task);

        // 作成したタスクをユーザー情報と一緒に返す
        $task->load('user');


        return response()->json([
            'success' => true,
            'task' => $task,
            'message' => 'タスクを作成しました'
        ]);
    }

    /**
     * タスクの詳細情報を取得
     */
    public function show(Project $project, Task $task)
    {
        // タスクが指定されたプロジェクトに属するかチェック
        if ($task->project_id !== $project->id) {
            abort(404);
        }

        $task->load(['user', 'parent', 'children']);

        return response()->json($task);
    }

    /**
     * タスク情報を更新
     * ガントチャートでドラッグ＆ドロップした日付変更や、進捗更新に使用
     */
    public function update(Request $request, Project $project, Task $task)
    {
        // タスクが指定されたプロジェクトに属するかチェック
        if ($task->project_id !== $project->id) {
            abort(404);
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'user_id' => 'nullable|exists:users,id',
            'status' => 'sometimes|in:todo,in_progress,done',
            'planned_start_date' => 'nullable|date',
            'planned_end_date' => 'nullable|date|after_or_equal:planned_start_date',
            'actual_start_date' => 'nullable|date',
            'actual_end_date' => 'nullable|date',
            'planned_effort' => 'nullable|numeric|min:0',
            'actual_effort' => 'nullable|numeric|min:0',
            'position' => 'sometimes|integer|min:0',
        ]);

        // 実際の開始日が設定された場合、ステータスを自動更新
        if (isset($validated['actual_start_date']) && !$task->actual_start_date) {
            $validated['status'] = 'in_progress';
        }

        // 実際の終了日が設定された場合、ステータスを完了に更新
        if (isset($validated['actual_end_date']) && !$task->actual_end_date) {
            $validated['status'] = 'done';
        }

        $originalUserId = $task->user_id; // 更新前の担当者IDを保持

        $task->update($validated);
        $task->load('user');

        // 担当者が変更された場合、イベントを発行して通知を送る
        if ($task->user_id !== $originalUserId) {
            TaskAssigned::dispatch($task);
        }

        return response()->json([
            'success' => true,
            'task' => $task,
            'message' => 'タスクを更新しました'
        ]);
    }

    /**
     * タスクの削除
     */
    public function destroy(Project $project, Task $task)
    {
        if ($task->project_id !== $project->id) {
            abort(404);
        }

        // 子タスクがある場合は削除を防ぐ（オプション）
        if ($task->children()->count() > 0) {
            return response()->json([
                'error' => '子タスクが存在するため削除できません。先に子タスクを削除してください。'
            ], 422);
        }

        $task->delete();

        return response()->json([
            'success' => true,
            'message' => 'タスクを削除しました'
        ]);
    }

    /**
     * タスクの並び順を一括更新
     * ドラッグ＆ドロップでタスクの順序を変更した際に使用
     */
    public function updatePositions(Request $request, Project $project)
    {
        $validated = $request->validate([
            'tasks' => 'required|array',
            'tasks.*.id' => 'required|exists:tasks,id',
            'tasks.*.position' => 'required|integer|min:0',
        ]);

        foreach ($validated['tasks'] as $taskData) {
            $task = Task::find($taskData['id']);

            // プロジェクトの整合性チェック
            if ($task->project_id !== $project->id) {
                continue;
            }

            $task->update(['position' => $taskData['position']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'タスクの並び順を更新しました'
        ]);
    }

    /**
     * プロジェクトの進捗サマリーを取得
     * ダッシュボードで使用
     */
    public function getProjectSummary(Project $project)
    {
        $tasks = Task::where('project_id', $project->id)->get();

        $summary = [
            'total_tasks' => $tasks->count(),
            'completed_tasks' => $tasks->where('status', 'done')->count(),
            'in_progress_tasks' => $tasks->where('status', 'in_progress')->count(),
            'todo_tasks' => $tasks->where('status', 'todo')->count(),
            'total_planned_effort' => $tasks->sum('planned_effort'),
            'total_actual_effort' => $tasks->sum('actual_effort'),
            'progress_percentage' => $tasks->count() > 0
                ? round(($tasks->where('status', 'done')->count() / $tasks->count()) * 100, 1)
                : 0,
        ];

        return response()->json($summary);
    }

    public function getGanttData(Project $project)
    {
        // [修正] parent_id が null のタスクだけでなく、プロジェクトの全タスクを取得する
        $tasks = $project->tasks()->with('user')->orderBy('position')->get();

        $ganttTasks = $tasks->map(function ($task) {

            // [修正] 期間(duration)を計算する。日付がなければ1日とする。
            $duration = 1;
            if ($task->planned_start_date && $task->planned_end_date) {
                // diffInDaysは純粋な差なので、+1して日数を数える
                $duration = $task->planned_start_date->diffInDays($task->planned_end_date) + 1;
            }

            return [
                'id'       => $task->id,
                'text'     => $task->title, // [修正] WBS番号は除外し、純粋なタイトルのみにする
                'start_date' => $task->planned_start_date?->format('Y-m-d'),
                // 'end_date' => $task->planned_end_date?->format('Y-m-d'), // durationがあるのでend_dateは不要
                'duration' => $duration,
                'progress' => $task->progress_percentage / 100, // dhtmlxは0から1の小数で進捗を表す
                'parent'   => $task->parent_id ?? 0, // [修正] 親ID。ルートタスクは '0'
                // 'open'     => true, // 最初から子タスクを開いておく場合は true を追加

                // 以下はカスタムプロパティとして追加可能
                'user'     => $task->user?->name ?? '未割り当て',
                'status'   => $task->status_label,
            ];
        })->filter(function ($task) {
            // [修正] 開始日が設定されていないタスクはガントチャートに表示しない
            return !is_null($task['start_date']);
        });

        return response()->json([
            'data'  => $ganttTasks->values(), // dhtmlxは 'data' キーでラップされた配列を期待する
            'links' => [] // タスク間の依存関係（将来の機能拡張用）
        ]);
    }

    /**
     * タスクの一括更新（ガントチャートでのドラッグ&ドロップ対応）
     */
    public function bulkUpdate(Request $request, Project $project)
    {
        $validated = $request->validate([
            'tasks' => 'required|array',
            'tasks.*.id' => 'required|exists:tasks,id',
            'tasks.*.start_date' => 'nullable|date',
            'tasks.*.end_date' => 'nullable|date',
            'tasks.*.progress' => 'nullable|numeric|min:0|max:1',
        ]);

        foreach ($validated['tasks'] as $taskData) {
            $task = Task::find($taskData['id']);

            // プロジェクトの整合性チェック
            if ($task->project_id !== $project->id) {
                continue;
            }

            $updateData = [];
            if (isset($taskData['start_date'])) {
                $updateData['planned_start_date'] = $taskData['start_date'];
            }
            if (isset($taskData['end_date'])) {
                $updateData['planned_end_date'] = $taskData['end_date'];
            }
            if (isset($taskData['progress'])) {
                // 進捗率からステータスを自動更新
                $progress = $taskData['progress'];
                if ($progress == 1.0) {
                    $updateData['status'] = 'done';
                } elseif ($progress > 0) {
                    $updateData['status'] = 'in_progress';
                } else {
                    $updateData['status'] = 'todo';
                }
            }

            if (!empty($updateData)) {
                $task->update($updateData);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'タスクを一括更新しました'
        ]);
    }
}
