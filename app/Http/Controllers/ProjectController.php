<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    /**
     * プロジェクト一覧表示
     * タスク統計も一緒に取得して表示
     */
    public function index()
    {
        $projects = Project::with(['createdBy'])
            ->withCount([
                'tasks', // 総タスク数
                'tasks as completed_tasks_count' => function($query) {
                    $query->where('status', 'done');
                }
            ])
            ->orderBy('created_at', 'desc')
            ->paginate(12); // 1ページに12件表示

        return view('projects.index', compact('projects'));
    }

    /**
     * プロジェクト作成画面
     */
    public function create()
    {
        return view('projects.create');
    }

    /**
     * プロジェクト保存
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $project = Project::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('projects.index')
            ->with('success', 'プロジェクト「' . $project->name . '」を作成しました。');
    }

    /**
     * プロジェクト詳細表示
     */
    public function show(Project $project)
    {
        $project->load(['tasks.user', 'createdBy']);

        // プロジェクトの統計情報を計算
        $stats = [
            'total_tasks' => $project->tasks->count(),
            'completed_tasks' => $project->tasks->where('status', 'done')->count(),
            'in_progress_tasks' => $project->tasks->where('status', 'in_progress')->count(),
            'todo_tasks' => $project->tasks->where('status', 'todo')->count(),
            'total_planned_effort' => $project->tasks->sum('planned_effort'),
            'total_actual_effort' => $project->tasks->sum('actual_effort'),
            'overdue_tasks' => $project->tasks->filter(function($task) {
                return $task->is_delayed;
            })->count(),
        ];

        return view('projects.show', compact('project', 'stats'));
    }

    /**
     * プロジェクト編集画面
     */
    public function edit(Project $project)
    {
        // プロジェクト作成者のみ編集可能
        if ($project->created_by !== Auth::id()) {
            abort(403, 'このプロジェクトを編集する権限がありません。');
        }

        return view('projects.edit', compact('project'));
    }

    /**
     * プロジェクト更新
     */
    public function update(Request $request, Project $project)
    {
        // プロジェクト作成者のみ更新可能
        if ($project->created_by !== Auth::id()) {
            abort(403, 'このプロジェクトを更新する権限がありません。');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $project->update($validated);

        return redirect()->route('projects.index')
            ->with('success', 'プロジェクト「' . $project->name . '」を更新しました。');
    }

    /**
     * プロジェクト削除
     */
    public function destroy(Project $project)
    {
        // プロジェクト作成者のみ削除可能
        if ($project->created_by !== Auth::id()) {
            abort(403, 'このプロジェクトを削除する権限がありません。');
        }

        $projectName = $project->name;

        // 関連するタスクも自動的に削除される（外部キー制約のcascade設定による）
        $project->delete();

        return redirect()->route('projects.index')
            ->with('success', 'プロジェクト「' . $projectName . '」を削除しました。');
    }

    /**
     * プロジェクトダッシュボード（統計情報の詳細表示）
     */
    public function dashboard(Project $project)
    {
        $project->load(['tasks.user']);

        // 詳細な統計情報を計算
        $stats = [
            'total_tasks' => $project->tasks->count(),
            'completed_tasks' => $project->tasks->where('status', 'done')->count(),
            'in_progress_tasks' => $project->tasks->where('status', 'in_progress')->count(),
            'todo_tasks' => $project->tasks->where('status', 'todo')->count(),
            'total_planned_effort' => $project->tasks->sum('planned_effort'),
            'total_actual_effort' => $project->tasks->sum('actual_effort'),
            'overdue_tasks' => $project->tasks->filter(function($task) {
                return $task->is_delayed;
            })->count(),
            'progress_percentage' => $project->tasks->count() > 0
                ? round(($project->tasks->where('status', 'done')->count() / $project->tasks->count()) * 100, 1)
                : 0,
        ];

        // 担当者別の統計
        $userStats = $project->tasks->groupBy('user_id')->map(function($tasks, $userId) {
            $user = $tasks->first()->user;
            return [
                'user' => $user,
                'total_tasks' => $tasks->count(),
                'completed_tasks' => $tasks->where('status', 'done')->count(),
                'in_progress_tasks' => $tasks->where('status', 'in_progress')->count(),
                'planned_effort' => $tasks->sum('planned_effort'),
                'actual_effort' => $tasks->sum('actual_effort'),
            ];
        });

        // 期限が近いタスク（7日以内）
        $upcomingTasks = $project->tasks->filter(function($task) {
            return $task->planned_end_date &&
                   $task->planned_end_date->diffInDays(now(), false) <= 7 &&
                   $task->status !== 'done';
        })->sortBy('planned_end_date');

        return view('projects.dashboard', compact('project', 'stats', 'userStats', 'upcomingTasks'));
    }
}
