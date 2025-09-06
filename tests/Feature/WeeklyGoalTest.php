<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WeeklyGoal;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WeeklyGoalTest extends TestCase
{
    use RefreshDatabase;

    private User $userA;
    private User $userB;
    private WeeklyGoal $goalOfUserA;
    private int $year;
    private int $week;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userA = User::factory()->create();
        $this->userB = User::factory()->create();

        // 週の真ん中、水曜日を基準にする
        $targetDate = Carbon::now()->startOfWeek(Carbon::MONDAY)->addDays(2);
        $this->year = $targetDate->year;
        $this->week = $targetDate->weekOfYear;

        // ユーザーAに、今週の目標を作成しておく
        $this->goalOfUserA = WeeklyGoal::factory()->create([
            'user_id' => $this->userA->id,
            'year' => $this->year,
            'week_number' => $this->week,
        ]);
    }

    public function test_ユーザーは自分の週目標編集画面にアクセスできる(): void
    {
        $this->actingAs($this->userA)
            ->get(route('weekly-goals.edit', [
                'user' => $this->userA,
                'year' => $this->year,
                'week_number' => $this->week,
            ]))
            ->assertOk();
    }

    public function test_ユーザーは他人の週目標編集画面にアクセスできない(): void
    {
        $this->actingAs($this->userB) // 他人(Bさん)でログイン
            ->get(route('weekly-goals.edit', [
                'user' => $this->userA, // Aさんの目標を編集しようとする
                'year' => $this->year,
                'week_number' => $this->week,
            ]))
            ->assertForbidden();
    }

    public function test_ユーザーは自分の週目標を保存できる(): void
    {
        $goalData = [
            'goal_this_week' => 'テストを完璧にする',
            'plan_next_week' => '新しい機能を実装する',
        ];

        $this->actingAs($this->userA)
            ->post(route('weekly-goals.storeOrUpdate', [
                'user' => $this->userA,
                'year' => $this->year,
                'week_number' => $this->week,
            ]), $goalData);

        $this->assertDatabaseHas('weekly_goals', [
            'id' => $this->goalOfUserA->id,
            'goal_this_week' => 'テストを完璧にする',
        ]);
    }

    public function test_ユーザーは他人の週目標を保存できない(): void
    {
        $goalData = ['goal_this_week' => '不正な更新'];

        $this->actingAs($this->userB) // 他人(Bさん)でログイン
            ->post(route('weekly-goals.storeOrUpdate', [
                'user' => $this->userA, // Aさんの目標を更新しようとする
                'year' => $this->year,
                'week_number' => $this->week,
            ]), $goalData)
            ->assertForbidden();
    }
}
