<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $generalUser;

    protected function setUp(): void
    {
        parent::setUp();

        // 各テストの前に実行されるメソッドで、ユーザーを作成してプロパティにセット
        $this->admin = User::factory()->create(['is_admin' => true]);
        $this->generalUser = User::factory()->create(['is_admin' => false]);
    }

    /** @test */
    public function test_非管理者はユーザー管理画面にアクセスできない(): void
    {
        $this->actingAs($this->generalUser)
            ->get('/admin/users')
            ->assertForbidden();
    }

    /** @test */
    public function test_管理者はユーザー管理画面にアクセスできる(): void
    {
        $this->actingAs($this->admin)
            ->get('/admin/users')
            ->assertOk();
    }

    /**
     * @test
     * @see \App\Http\Controllers\Admin\UserController::store()
     */
    public function test_管理者は有効なデータで新しいユーザーを作成できる(): void
    {
        // 準備：テストで使うデータを用意する
        $userData = [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'is_admin' => false,
        ];

        // 実行：管理者としてユーザー作成エンドポイントにPOSTリクエストを送る
        $response = $this->actingAs($this->admin)->post(route('admin.users.store'), $userData);

        // 検証
        // 1. ユーザー一覧ページにリダイレクトされているか？
        $response->assertRedirect(route('admin.users.index'));

        // 2. データベースの 'users' テーブルに、送信したデータが正しく保存されているか？
        //    (パスワードはハッシュ化されるので、直接比較しない)
        $this->assertDatabaseHas('users', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
        ]);
    }

    /**
     * @test
     * @see \App\Http\Requests\Admin\User\StoreRequest
     */
    public function test_ユーザー作成時にバリデーションが機能する(): void
    {
        // 準備：意図的にバリデーションエラーを引き起こすデータを用意する (名前が空)
        $invalidUserData = [
            'name' => '', // 名前を空にする
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];

        // 実行：管理者として、不正なデータでPOSTリクエストを送る
        $response = $this->actingAs($this->admin)->post(route('admin.users.store'), $invalidUserData);

        // 検証
        // 1. セッションに 'name' フィールドのエラーメッセージが含まれているか？
        $response->assertSessionHasErrors('name');

        // 2. データベースにこのユーザーデータが保存されていないことを確認する
        $this->assertDatabaseMissing('users', [
            'email' => 'test@example.com',
        ]);
    }


    /**
     * @test
     * @see \App\Http\Controllers\Admin\UserController::update()
     */
    public function test_管理者はユーザー情報を更新できる(): void
    {
        // 準備: 更新対象のユーザーを1人用意する
        $targetUser = User::factory()->create();

        // 準備: 更新するデータ
        $updateData = [
            'name' => '更新後のユーザー名',
            'email' => 'updated@example.com',
            'is_admin' => true,
        ];

        // 実行: 管理者としてユーザー更新エンドポイントにPATCHリクエストを送信
        $response = $this->actingAs($this->admin)
            ->patch(route('admin.users.update', $targetUser), $updateData);

        // 検証
        // 1. ユーザー一覧ページにリダイレクトされるか
        $response->assertRedirect(route('admin.users.index'));
        // 2. データベースの値が正しく更新されているか
        $this->assertDatabaseHas('users', [
            'id' => $targetUser->id,
            'name' => '更新後のユーザー名',
            'email' => 'updated@example.com',
            'is_admin' => true,
        ]);
    }

    /**
     * @test
     * @see \App\Http\Requests\Admin\User\UpdateRequest
     */
    public function test_ユーザー更新時にバリデーションが機能する(): void
    {
        // 準備: 更新対象のユーザー
        $targetUser = User::factory()->create(['email' => 'original@example.com']);
        // 準備: 別のユーザーが既に使用しているメールアドレス
        $anotherUser = User::factory()->create(['email' => 'existing@example.com']);

        // 準備: バリデーションエラーを起こすデータ (emailがユニークではない)
        $invalidUpdateData = [
            'name' => '更新テスト',
            'email' => $anotherUser->email, // 既存のメールアドレス
        ];

        // 実行
        $response = $this->actingAs($this->admin)
            ->patch(route('admin.users.update', $targetUser), $invalidUpdateData);

        // 検証
        // 1. セッションに 'email' フィールドのエラーが含まれているか
        $response->assertSessionHasErrors('email');
        // 2. 更新対象ユーザーのメールアドレスが変更されていないことを確認
        $this->assertDatabaseHas('users', [
            'id' => $targetUser->id,
            'email' => 'original@example.com',
        ]);
    }

    /**
     * @see \App\Http\Controllers\Admin\UserController::destroy()
     */
    public function test_管理者はユーザーを削除できる(): void
    {
        // 準備: 削除対象のユーザーを1人作成
        $targetUser = User::factory()->create();

        // 実行: 管理者としてユーザー削除エンドポイントにDELETEリクエストを送信
        $response = $this->actingAs($this->admin)
            ->delete(route('admin.users.destroy', $targetUser));

        // 検証
        // 1. ユーザー一覧ページにリダイレクトされるか
        $response->assertRedirect(route('admin.users.index'));

        // 2. データベースからユーザーが削除されているか (Soft Deleteの場合は少し違う)
        $this->assertSoftDeleted('users', [
            'id' => $targetUser->id,
        ]);

    }
}
