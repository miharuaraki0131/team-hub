<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('knowledges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->string('title', 255)->comment('タイトル');
            $table->text('body')->comment('内容');
            $table->boolean('is_pinned')->default(false)->comment('ピン留め');
            $table->datetime('published_at')->nullable()->comment('公開日時');
            $table->datetime('expired_at')->nullable()->comment('期限日時');
            $table->string('category')->nullable()->comment('カテゴリー');
            $table->integer('view_count')->default(0);
            $table->enum('priority', ['low', 'normal', 'high'])->default('normal');
            $table->timestamps();
            $table->softDeletes();

            // インデックス
            $table->index(['published_at', 'expired_at']);
            $table->index(['is_pinned', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('knowledges');
    }
};
