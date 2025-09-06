<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventTest extends TestCase
{
    use RefreshDatabase;

    private User $userA;
    private User $userB;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userA = User::factory()->create();
        $this->userB = User::factory()->create();
        $this->admin = User::factory()->create(['is_admin' => true]);
    }

    public function test_ログインユーザーはカレンダーを作成できる(): void
    {
        $eventData = [
            'title' => 'テストイベント',
            'body' => 'これはテストです',
            'start_date' => '2025-09-10',
            'start_time' => '10:00',
            'end_date' => '2025-09-10',
            'end_time' => '11:00',
            'is_all_day' => false,
            'category' => 'テストカテゴリ',
            'visibility' => 'public',
            'color' => '#3b82f6',
        ];

        $this->actingAs($this->userA)
            ->post(route('events.store'), $eventData);

        $this->assertDatabaseHas('events', ['title' => 'テストイベント']);
    }

    public function test_ユーザーは自分のイベントを編集できる(): void
    {
        $event = Event::factory()->create(['user_id' => $this->userA->id]);
        $this->actingAs($this->userA)
            ->get(route('events.edit', $event))
            ->assertOk();
    }

    public function test_ユーザーは他人のイベントを編集できない(): void
    {
        $event = Event::factory()->create(['user_id' => $this->userA->id]);
        $this->actingAs($this->userB)
            ->get(route('events.edit', $event))
            ->assertForbidden();
    }

    public function test_ユーザーは自分のプライベートイベント詳細を閲覧できる(): void
    {
        $event = Event::factory()->create(['user_id' => $this->userA->id, 'visibility' => 'private']);
        $this->actingAs($this->userA)
            ->get(route('events.show', $event))
            ->assertOk();
    }

    public function test_ユーザーは他人のプライベートイベント詳細を閲覧できない(): void
    {
        $event = Event::factory()->create(['user_id' => $this->userA->id, 'visibility' => 'private']);
        $this->actingAs($this->userB)
            ->get(route('events.show', $event))
            ->assertNotFound(); // abort(404) なので assertNotFound を使う
    }

    public function test_ユーザーは自分のイベントを削除できる(): void
    {
        $event = Event::factory()->create(['user_id' => $this->userA->id]);
        $this->actingAs($this->userA)
            ->delete(route('events.destroy', $event));
        $this->assertSoftDeleted('events', ['id' => $event->id]);
    }

    public function test_管理者は他人のイベントを削除できる(): void
    {
        $event = Event::factory()->create(['user_id' => $this->userA->id]);
        $this->actingAs($this->admin) // 管理者でログイン
            ->delete(route('events.destroy', $event));
        $this->assertSoftDeleted('events', ['id' => $event->id]);
    }
    public function test_getEvents_APIは適切なイベントを返す(): void
    {
        // 準備: 範囲内のイベント、範囲外のイベント、他人のプライベートイベントを作成
        $start = Carbon::parse('2025-09-08')->startOfWeek();
        $end = Carbon::parse('2025-09-08')->endOfWeek();

        $visibleEvent = Event::factory()->create([
            'user_id' => $this->userA->id,
            'visibility' => 'public',
            'start_datetime' => $start->copy()->addDay(),
            'end_datetime' => $start->copy()->addDay()->addHour(),
        ]);

        Event::factory()->create([ // 範囲外のイベント
            'user_id' => $this->userA->id,
            'start_datetime' => $start->copy()->subDay(),
            'end_datetime' => $start->copy()->subDay()->addHour(),
        ]);

        Event::factory()->create([ // 他人のプライベートイベント
            'user_id' => $this->userB->id,
            'visibility' => 'private',
            'start_datetime' => $start->copy()->addDay(),
            'end_datetime' => $start->copy()->addDay()->addHour(),
        ]);

        // 実行 & 検証
        $response = $this->actingAs($this->userA)
            ->getJson(route('events.json', ['start' => $start->toIso8601String(), 'end' => $end->toIso8601String()]));

        $response->assertOk();
        $response->assertJsonCount(1); // 見えるべきイベントは1つだけ
        $response->assertJsonFragment(['id' => $visibleEvent->id]); // そのイベントが含まれているか
    }

    public function test_ユーザーはAJAXで自分のイベントを更新できる(): void
    {
        $event = Event::factory()->create(['user_id' => $this->userA->id]);
        $updateData = [
            'title' => 'AJAX更新テスト',
            'body' => $event->body,
            'start_date' => $event->start_datetime->format('Y-m-d'),
            'start_time' => $event->start_datetime->format('H:i'),
            'end_date' => $event->end_datetime->format('Y-m-d'),
            'end_time' => $event->end_datetime->format('H:i'),
            'is_all_day' => $event->is_all_day,
            'category' => $event->category,
            'visibility' => $event->visibility,
            'color' => $event->color,
        ];

        // actingAsでログインし、patchJsonでJSONリクエストを送信
        $this->actingAs($this->userA)
            ->patchJson(route('events.update', $event), $updateData)
            ->assertOk(); // 200 OKが返ることを確認

        $this->assertDatabaseHas('events', ['id' => $event->id, 'title' => 'AJAX更新テスト']);
    }
}
