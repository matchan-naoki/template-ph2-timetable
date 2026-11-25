<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 既存のデータベースにroomsがある場合は作成しない
        if (Schema::hasTable('rooms')) {
            return;
        }

        Schema::create('rooms', function (Blueprint $table) {
            $table->text('code')->primary();
            $table->text('building');
            $table->integer('floor');
            $table->integer('capacity');
        });
    }

    public function down(): void
    {
        // 既存のroomsテーブルを誤って削除しないため、
        // このマイグレーションでは削除処理を行わない
    }
};
