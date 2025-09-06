<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        // ユーザーを何人か作成しておく
        $user1 = User::factory()->create(['name' => '美晴']);
        $user2 = User::factory()->create();

        // 1. プロジェクトを作成
        $project = Project::factory()->create([
            'name' => 'TeamHub Ver.2.0 開発',
            'created_by' => $user1->id,
        ]);

        // 2. 親タスクを作成
        $parentTask1 = Task::factory()->create([
            'project_id' => $project->id,
            'title' => 'WBS/タスク管理機能 (Task Hub) の実装',
            'user_id' => $user1->id,
            'created_by' => $user1->id,
        ]);

        // 3. 子タスクを作成
        Task::factory()->create([
            'project_id' => $project->id,
            'parent_id' => $parentTask1->id, // 親タスクを指定
            'title' => 'DB設計とマイグレーション作成',
            'user_id' => $user1->id,
            'created_by' => $user1->id,
            'status' => 'done',
        ]);

        Task::factory()->create([
            'project_id' => $project->id,
            'parent_id' => $parentTask1->id,
            'title' => 'APIエンドポイントの作成',
            'user_id' => $user2->id,
            'created_by' => $user1->id,
            'status' => 'in_progress',
        ]);

        // 別の親タスク
        Task::factory()->create([
            'project_id' => $project->id,
            'title' => 'README.md の完成',
            'user_id' => $user2->id,
            'created_by' => $user1->id,
        ]);
    }
}
