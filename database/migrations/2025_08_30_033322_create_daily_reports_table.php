<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('daily_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->date('report_date')->comment('報告日');
            $table->text('summary_today')->nullable()->comment('今日やったこと');
            $table->text('discrepancy')->nullable()->comment('目標との差異');
            $table->text('summary_tomorrow')->nullable()->comment('明日やること');
            $table->text('issues_thoughts')->nullable()->comment('困っていることや感想');
            $table->timestamps();
            $table->softDeletes();

            // 複合ユニークキーで、一人のユーザーが同じ日に2つ日報を作れないようにする
            $table->unique(['user_id', 'report_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_reports');
    }
};
