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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();

            // レビューを投稿したユーザー
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            // レビュー対象の授業
            $table->foreignId('course_id')
                ->constrained()
                ->cascadeOnDelete();

            // 評価（1〜5）
            $table->integer('rating');

            // レビューの内容
            $table->text('comment');

            // 投稿日時・更新日時
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
