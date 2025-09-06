<?php

namespace Tests\Feature;

use App\Models\DailyReport;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WeeklyReportTest extends TestCase
{
    use RefreshDatabase;

    private User $userA;
    private User $userB;
    private DailyReport $reportOfUserA;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userA = User::factory()->create();
        $this->userB = User::factory()->create();
        $wednesday = Carbon::now()->startOfWeek(Carbon::MONDAY)->addDays(2);

        $this->reportOfUserA = DailyReport::factory()->create([
            'user_id' => $this->userA->id,
            'report_date' => $wednesday,
        ]);
    }

    /** @test */
    public function test_ログインユーザーは他人の週報を閲覧できる(): void
    {
        $targetDate = Carbon::parse($this->reportOfUserA->report_date);
        $year = $targetDate->year;
        $week = $targetDate->weekOfYear;

        $response = $this->actingAs($this->userB)
            ->get(route('weekly-reports.show', [
                'user' => $this->userA,
                'year' => $year,
                'week_number' => $week,
            ]));

        $response->assertOk();

        // contains() の代わりに has() を使う ↓↓↓
        $response->assertViewHas('dailyReports', function ($reports) {
            $expectedDateKey = Carbon::parse($this->reportOfUserA->report_date)->format('Y-m-d');
            return $reports->has($expectedDateKey);
        });
    }

    public function test_ユーザーは自分の日報編集画面にアクセスできる(): void
    {
        $reportDate = Carbon::parse($this->reportOfUserA->report_date)->format('Y-m-d');

        $this->actingAs($this->userA)
            ->get(route('daily-reports.edit', ['user' => $this->userA, 'date' => $reportDate]))
            ->assertOk();
    }

    // ユーザーは他人の日報編集画面にアクセスできない (こちらも同様)
    public function test_ユーザーは他人の日報編集画面にアクセスできない(): void
    {
        $reportDate = Carbon::parse($this->reportOfUserA->report_date)->format('Y-m-d');

        $this->actingAs($this->userB)
            ->get(route('daily-reports.edit', ['user' => $this->userA, 'date' => $reportDate]))
            ->assertForbidden();
    }


    // ★★★ ここが今回失敗したテスト ★★★
    public function test_ユーザーは自分の日報を保存できる(): void
    {
        $reportDate = Carbon::parse($this->reportOfUserA->report_date)->format('Y-m-d');

        $reportData = [
            'summary_today' => 'テストで更新しました',
            'discrepancy' => '差分はありません',
            'summary_tomorrow' => '明日はテストを書きます',
            'issues_thoughts' => '課題はありません',
        ];

        $this->actingAs($this->userA)
            ->post(route('daily-reports.storeOrUpdate', ['user' => $this->userA, 'date' => $reportDate]), $reportData);

        $this->assertDatabaseHas('daily_reports', [
            'id' => $this->reportOfUserA->id,
            'summary_today' => 'テストで更新しました',
        ]);
    }


    // ユーザーは他人の日報を保存できない (こちらも同様)
    public function test_ユーザーは他人の日報を保存できない(): void
    {
        $reportDate = Carbon::parse($this->reportOfUserA->report_date)->format('Y-m-d');

        $reportData = [
            'summary_today' => 'これは不正な更新データです',
            'discrepancy' => '差分',
            'summary_tomorrow' => '明日の予定',
            'issues_thoughts' => '課題・感想',
        ];

        $this->actingAs($this->userB)
            ->post(route('daily-reports.storeOrUpdate', ['user' => $this->userA, 'date' => $reportDate]), $reportData)
            ->assertForbidden();
    }
}
