<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 既存のcoursesテーブルがあれば作成しない
        if (Schema::hasTable('courses')) {
            return;
        }

        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->text('code');
            $table->text('name');
            $table->text('teacher')->nullable();
            $table->integer('day');
            $table->integer('period');
            $table->text('room');
            $table->integer('credits');
            $table->boolean('required');
        });
    }

    public function down(): void
    {
        // 既存のcoursesテーブルを誤って削除しないため、
        // ロールバック時には何もしない
    }
};