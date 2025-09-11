<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    use RefreshDatabase;

    private User $userA;
    private User $userB;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userA = User::factory()->create();
        $this->userB = User::factory()->create();
    }

    public function test_ログインユーザーはプロジェクト一覧を閲覧できる(): void
    {
        // 準備: userA にプロジェクトを1つ作成させておく
        Project::factory()->create(['created_by' => $this->userA->id]);

        $response = $this->actingAs($this->userA)->get(route('projects.index'));

        $response->assertOk();
        $response->assertViewHas('projects'); // ビューに 'projects' 変数が渡されているか
    }

    public function test_ユーザーは有効なデータでプロジェクトを作成できる(): void
    {
        $projectData = [
            'name' => '新しいテストプロジェクト',
            'description' => 'これは説明文です。',
        ];

        $this->actingAs($this->userA)
            ->post(route('projects.store'), $projectData);

        $this->assertDatabaseHas('projects', [
            'name' => '新しいテストプロジェクト',
            'created_by' => $this->userA->id, // 作成者が正しいか
        ]);
    }

    public function test_プロジェクト作成時にバリデーションが機能する(): void
    {
        $invalidData = ['name' => '']; // 名前を空にする

        $response = $this->actingAs($this->userA)
            ->post(route('projects.store'), $invalidData);

        $response->assertSessionHasErrors('name'); // 'name' でエラーが出るか
    }

    public function test_プロジェクト作成者は自分のプロジェクトを更新できる(): void
    {
        // 準備: userA が作成したプロジェクト
        $project = Project::factory()->create(['created_by' => $this->userA->id]);

        $updateData = ['name' => '更新されたプロジェクト名'];

        $this->actingAs($this->userA) // 作成者本人でログイン
            ->patch(route('projects.update', $project), $updateData);

        $this->assertDatabaseHas('projects', ['id' => $project->id, 'name' => '更新されたプロジェクト名']);
    }

    public function test_他人は他人のプロジェクトを更新できない(): void
    {
        // 準備: userA が作成したプロジェクト
        $project = Project::factory()->create(['created_by' => $this->userA->id]);

        $updateData = ['name' => '不正な更新'];

        $this->actingAs($this->userB) // 他人でログイン
            ->patch(route('projects.update', $project), $updateData)
            ->assertForbidden(); // 403 Forbidden が返るか
    }

    public function test_プロジェクト作成者は自分のプロジェクトを削除できる(): void
    {
        $project = Project::factory()->create(['created_by' => $this->userA->id]);

        $this->actingAs($this->userA) // 作成者本人でログイン
            ->delete(route('projects.destroy', $project));

        // 論理削除されていることを確認
        $this->assertSoftDeleted('projects', ['id' => $project->id]);
    }

    public function test_他人は他人のプロジェクトを削除できない(): void
    {
        $project = Project::factory()->create(['created_by' => $this->userA->id]);

        $this->actingAs($this->userB) // 他人でログイン
            ->delete(route('projects.destroy', $project))
            ->assertForbidden(); // 403 Forbidden が返るか
    }
}
