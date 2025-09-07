<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $projects = Project::latest()->get(); // 作成日が新しい順に全てのプロジェクトを取得

        return view('projects.index', compact('projects'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project)
    {
        // このプロジェクトに属する、最上位のタスク（親がいないタスク）を取得し、
        // その子タスク（children）、さらにその子タスク（children.children）...を再帰的に事前読み込みする
        $tasks = $project->tasks()
            ->whereNull('parent_id')
            ->with('children') // 'children' はTaskモデルに定義したリレーション名
            ->orderBy('position')
            ->get();

        return view('projects.show', compact('project', 'tasks'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Project $project)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project)
    {
        //
    }
}
