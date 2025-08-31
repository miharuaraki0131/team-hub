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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->string('title'); // 予定のタイトル
            $table->text('body')->nullable(); // 詳細説明
            $table->datetime('start_datetime'); // 開始日時
            $table->datetime('end_datetime'); // 終了日時
            $table->boolean('is_all_day')->default(false); // 終日フラグ
            $table->string('category')->nullable(); // カテゴリー（会議、個人、チーム等）
            $table->enum('visibility', ['public', 'private'])->default('public'); // 公開設定
            $table->string('color', 7)->default('#3B82F6'); // 表示色（HEXカラー）
            $table->timestamps();
            $table->softDeletes();

            // インデックス
            $table->index(['start_datetime', 'end_datetime']);
            $table->index(['user_id', 'start_datetime']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
