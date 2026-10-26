<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('timetable_entries', function (Blueprint $table) {

            // 履修申請の状態
            $table->string('status')->default('pending');

            // 承認・却下された日時
            $table->timestamp('decided_at')->nullable();

        });
    }

    public function down(): void
    {
        Schema::table('timetable_entries', function (Blueprint $table) {

            $table->dropColumn(['status', 'decided_at']);

        });
    }
};
