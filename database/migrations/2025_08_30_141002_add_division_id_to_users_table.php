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
            // 'name'カラムの後に、division_idカラムを追加
            $table->foreignId('division_id')
                  ->nullable() // 既存のユーザーがいるため、最初はNULLを許容する
                  ->after('name')
                  ->constrained('divisions') // divisionsテーブルのidを参照する
                  ->onDelete('set null');   // もし部署が削除されたら、ユーザーのdivision_idをNULLにする
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // up()メソッドの、逆の操作を記述する
            $table->dropForeign(['division_id']); // まず外部キー制約を削除
            $table->dropColumn('division_id');  // 次にカラム自体を削除
        });
    }
};
