<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('timetable_entries', function (Blueprint $table) {
            // ユーザーIDを追加（外部キー）
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // 同じユーザーが同じ授業を重複登録できない
            $table->unique(['user_id', 'course_id']);
        });
    }

    public function down(): void
    {
        Schema::table('timetable_entries', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'course_id']);
            $table->dropConstrainedForeignId('user_id');
        });
    }
};
