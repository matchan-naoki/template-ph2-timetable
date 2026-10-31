<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();

            // 対象となる授業
$table->integer('course_id');

$table->foreign('course_id')
    ->references('id')
    ->on('courses')
    ->cascadeOnDelete();

            // 投稿した教務課ユーザー
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // お知らせの内容
            $table->string('title', 50);
            $table->string('body', 500);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
