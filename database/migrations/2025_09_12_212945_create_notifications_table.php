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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();

            // 通知を受け取るユーザーへの外部キー
            $table->foreignId('user_id')->constrained('users');

            // 通知の種類を文字列で格納 (例: 'task_assigned', 'report_mentioned')
            $table->string('type');

            // 関連データ（どのタスクか、誰からの通知か等）をJSON形式で格納
            $table->json('data');

            // 既読・未読を判定するためのタイムスタンプ（未読の場合はNULL）
            $table->timestamp('read_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
