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
        Schema::table('users', function (Blueprint $table) {
            // 'password'カラムの後に、is_adminカラムを追加
            $table->boolean('is_admin')
                  ->after('password') // パスワードの直後あたりが分かりやすい
                  ->default(false);   // デフォルトは「非管理者(false)」にする
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // up()メソッドの逆の操作
            $table->dropColumn('is_admin');
        });
    }
};
