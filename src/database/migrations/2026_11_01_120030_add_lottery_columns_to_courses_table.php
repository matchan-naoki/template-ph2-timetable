<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            // 履修申請の締切日時
            $table->timestamp('application_deadline')->nullable();

            // 抽選完了日時（二重抽選防止）
            $table->timestamp('lottery_completed_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn([
                'application_deadline',
                'lottery_completed_at',
            ]);
        });
    }
};
