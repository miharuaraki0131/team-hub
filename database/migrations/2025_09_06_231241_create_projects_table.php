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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // プロジェクト名
            $table->text('description')->nullable(); // プロジェクト説明
            $table->foreignId('created_by')->constrained('users'); // 作成者
            $table->timestamps();
            $table->softDeletes(); // 論理削除用
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
