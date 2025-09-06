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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->comment('プロジェクトID')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null')->comment('担当者ID'); //userが削除されたらnullにする
            $table->foreignId('parent_id')->nullable()->constrained('tasks')->onDelete('cascade')->comment('親タスクID'); //親タスクが削除されたら子タスクも削除
            $table->string('title')->comment('タスクタイトル');
            $table->text('description')->nullable()->comment('タスク説明');
            $table->string('status')->default('todo')->comment('タスクステータス'); // 例: todo, in_progress, done
            $table->date('planned_start_date')->nullable()->comment('予定開始日');
            $table->date('planned_end_date')->nullable()->comment('予定終了日');
            $table->date('actual_start_date')->nullable()->comment('実際の開始日');
            $table->date('actual_end_date')->nullable()->comment('実際の終了日');
            $table->decimal('planned_effort', 5, 2)->nullable()->comment('予定工数 (例: 999.99時間まで対応)');
            $table->decimal('actual_effort', 5, 2)->nullable()->comment('実際の工数 (例: 999.99時間まで対応)');
            $table->integer('position')->default(0)->comment('タスクの表示順序');
            $table->foreignId('created_by')->constrained('users')->comment('作成者ID');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
