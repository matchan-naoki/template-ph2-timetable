<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('room_reservations', function (Blueprint $table) {
            $table->id();

            // 予約した学生
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // 教室コード（rooms.code は文字列の主キー）
            $table->text('room_code');

            // 予約日と時限
            $table->date('date');
            $table->integer('period');

            $table->timestamps();

            // 存在する教室だけ予約できる
            $table->foreign('room_code')
                ->references('code')
                ->on('rooms')
                ->restrictOnDelete();

            // 同じ教室・日付・時限の二重予約をDBで防止
            $table->unique(
                ['room_code', 'date', 'period'],
                'room_reservations_unique_slot'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_reservations');
    }
};