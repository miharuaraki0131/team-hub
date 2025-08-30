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
            $table->text('summary_today')->comment('今日やったこと');
            $table->text('discrepancy')->comment('目標との差異');
            $table->text('summary_tomorrow')->comment('明日やること');
            $table->text('issues_thoughts')->comment('困っていることや感想');
            $table->softDeletes();
            $table->timestamps();
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
