<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private User $member;
    private User $otherUser;
    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();
        // 登場人物と、彼らが共有するプロジェクトを準備
        $this->owner = User::factory()->create();
        $this->member = User::factory()->create();
        $this->otherUser = User::factory()->create();

        $this->project = Project::factory()->create(['created_by' => $this->owner->id]);
    }

    public function test_プロジェクトメンバーはタスク一覧ページを閲覧できる(): void
    {
        // 準備: プロジェクトにタスクを1つ作成
        Task::factory()->create(['project_id' => $this->project->id]);

        $this->actingAs($this->owner) // プロジェクトオーナーでログイン
            ->get(route('tasks.index', $this->project))
            ->assertOk();
    }

    public function test_プロジェクトメンバーはタスクを作成できる(): void
    {
        $taskData = [
            'title' => 'API経由での新しいタスク',
            'user_id' => $this->member->id,
        ];

        $response = $this->actingAs($this->owner)
            ->postJson(route('tasks.store', $this->project), $taskData);

        $response->assertOk(); // 成功のJSONレスポンスが返るか
        $response->assertJson(['success' => true]); // 'success'キーがあるか
        $this->assertDatabaseHas('tasks', ['title' => 'API経由での新しいタスク']);
    }

    public function test_タスク作成時にバリデーションが機能する(): void
    {
        $invalidData = ['title' => '']; // titleを空にする

        $response = $this->actingAs($this->owner)
            ->postJson(route('tasks.store', $this->project), $invalidData);

        $response->assertStatus(422); // バリデーションエラー(422)が返るか
        $response->assertJsonValidationErrors('title'); // 'title'キーでエラーがあるか
    }

    public function test_プロジェクトメンバーはタスクを更新できる(): void
    {
        $task = Task::factory()->create(['project_id' => $this->project->id]);
        $updateData = ['title' => '更新されたタスク名'];

        $this->actingAs($this->owner)
            ->putJson(route('tasks.update', [$this->project, $task]), $updateData)
            ->assertOk();

        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'title' => '更新されたタスク名']);
    }

    public function test_プロジェクトメンバーは子タスクのないタスクを削除できる(): void
    {
        $task = Task::factory()->create(['project_id' => $this->project->id]);

        $this->actingAs($this->owner)
            ->deleteJson(route('tasks.destroy', [$this->project, $task]))
            ->assertOk();

        // TaskモデルはSoftDeletesを使っているので、論理削除を確認
        $this->assertSoftDeleted('tasks', ['id' => $task->id]);
    }

    public function test_子タスクを持つタスクは削除できない(): void
    {
        $parentTask = Task::factory()->create(['project_id' => $this->project->id]);
        $childTask = Task::factory()->create([
            'project_id' => $this->project->id,
            'parent_id' => $parentTask->id,
        ]);

        $response = $this->actingAs($this->owner)
            ->deleteJson(route('tasks.destroy', [$this->project, $parentTask]));

        $response->assertStatus(422); // 処理できないエンティティ(422)エラーが返るか
        $this->assertNotSoftDeleted('tasks', ['id' => $parentTask->id]); // DBから消えていないこと
    }
}
