<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('timetable_entries', function (Blueprint $table) {
            $table->dropUnique('timetable_entries_day_period_unique');
            $table->dropColumn(['day', 'period']);
        });
    }

    public function down(): void
    {
        Schema::table('timetable_entries', function (Blueprint $table) {
            $table->integer('day')->nullable();
            $table->integer('period')->nullable();
            $table->unique(['day', 'period']);
        });
    }
};