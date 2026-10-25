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
    Schema::create('favorites', function (Blueprint $table) {
        $table->id();

        // お気に入り登録したユーザー
        $table->foreignId('user_id')
            ->constrained('users')
            ->cascadeOnDelete();

        // お気に入り登録された授業
        $table->foreignId('course_id')
            ->constrained('courses')
            ->cascadeOnDelete();

        $table->timestamps();

        // 同じユーザーが同じ授業を重複登録できないようにする
        $table->unique(['user_id', 'course_id']);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('favorites');
    }
};
