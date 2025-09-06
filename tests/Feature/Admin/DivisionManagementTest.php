<?php

namespace Tests\Feature\Admin;

use App\Models\Division;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DivisionManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $generalUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['is_admin' => true]);
        $this->generalUser = User::factory()->create(['is_admin' => false]);
    }

    public function test_管理者は部署管理一覧画面にアクセスできる(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.divisions.index'))
            ->assertOk();
    }

    public function test_一般ユーザーは部署管理一覧画面にアクセスできない(): void
    {
        $this->actingAs($this->generalUser)
            ->get(route('admin.divisions.index'))
            ->assertForbidden();
    }

    public function test_管理者は有効なデータで新しい部署を作成できる(): void
    {
        $divisionData = [
            'name' => '開発部',
            'emails' => [ // 配列で複数のアドレスを送信
                'dev1@example.com',
                'dev2@example.com',
            ],
        ];

        $this->actingAs($this->admin)
            ->post(route('admin.divisions.store'), $divisionData);

        // divisionsテーブルにデータが存在することを確認
        $this->assertDatabaseHas('divisions', ['name' => '開発部']);

        // 作成された部署を取得
        $division = Division::where('name', '開発部')->first();

        // notification_destinationsテーブルにデータが存在し、division_idが正しいことを確認
        $this->assertDatabaseHas('notification_destinations', [
            'division_id' => $division->id,
            'email' => 'dev1@example.com',
        ]);
        $this->assertDatabaseHas('notification_destinations', [
            'division_id' => $division->id,
            'email' => 'dev2@example.com',
        ]);
    }


    public function test_部署作成時にバリデーションが機能する(): void
    {
        $invalidData = [
            'name' => '', // 名前を空にする
            'emails' => [
                'not-an-email', // メールアドレスではない
                'valid@example.com',
            ],
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.divisions.store'), $invalidData);

        // 各フィールドでセッションにエラーメッセージが含まれているか
        $response->assertSessionHasErrors(['name', 'emails.0']);

        // データベースにデータが保存されていないことを確認
        $this->assertDatabaseCount('divisions', 2); // setUp()で作られた2件のままのはず
        $this->assertDatabaseCount('notification_destinations', 0);
    }

    public function test_管理者は部署情報を更新できる(): void
    {
        // 準備: 更新対象の部署を作成し、既存の通知先もいくつか作成しておく
        $division = Division::factory()->create(['name' => '旧部署名']);
        $division->notificationDestinations()->create(['email' => 'keep@example.com']);
        $division->notificationDestinations()->create(['email' => 'delete@example.com']);

        // 準備: 更新データ (既存の一つを残し、一つを削除し、新しい二つを追加するシナリオ)
        $updateData = [
            'name' => '新部署名',
            'emails' => [
                'keep@example.com', // 既存のものを維持
                'new1@example.com', // 新規追加1
                'new2@example.com', // 新規追加2
            ],
        ];

        // 実行
        $this->actingAs($this->admin)
            ->patch(route('admin.divisions.update', $division), $updateData);

        // 検証
        // 1. 部署名が更新されているか
        $this->assertDatabaseHas('divisions', ['id' => $division->id, 'name' => '新部署名']);
        // 2. 削除されるべきメールアドレスが存在しないこと
        $this->assertDatabaseMissing('notification_destinations', ['email' => 'delete@example.com']);
        // 3. 維持されるべきメールアドレスが存在すること
        $this->assertDatabaseHas('notification_destinations', ['email' => 'keep@example.com']);
        // 4. 新しく追加されたメールアドレスが存在すること
        $this->assertDatabaseHas('notification_destinations', ['email' => 'new1@example.com']);
        $this->assertDatabaseHas('notification_destinations', ['email' => 'new2@example.com']);
    }

    public function test_部署更新時にバリデーションが機能する(): void
    {
        $division = Division::factory()->create();

        $invalidData = [
            'name' => '', // 名前を空にする
            'emails' => ['not-an-email'],
        ];

        $response = $this->actingAs($this->admin)
            ->patch(route('admin.divisions.update', $division), $invalidData);

        $response->assertSessionHasErrors(['name', 'emails.0']);
    }

    public function test_管理者は部署を削除できる(): void
    {
        // 準備: 削除対象の部署と、それに紐づく通知先を作成
        $division = Division::factory()->create();
        $division->notificationDestinations()->create(['email' => 'test1@example.com']);
        $division->notificationDestinations()->create(['email' => 'test2@example.com']);

        // 削除前のDB状態を確認
        $this->assertDatabaseHas('divisions', ['id' => $division->id]);
        $this->assertDatabaseCount('notification_destinations', 2);

        // 実行
        $this->actingAs($this->admin)
            ->delete(route('admin.divisions.destroy', $division));

        // 検証: 部署が論理削除されていること
        $this->assertSoftDeleted('divisions', ['id' => $division->id]);

        // 検証: 関連する通知先が物理削除されていること
        // (通常、関連データは CASCADE DELETE で物理削除されることを期待する)
        $this->assertDatabaseCount('notification_destinations', 0);
    }
}
