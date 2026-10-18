<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('timetable_entries', function (Blueprint $table) {
            $table->integer('day');
            $table->integer('period');
            $table->string('name');
            $table->string('room');

            $table->unique(['day', 'period']);
        });
    }

    public function down(): void
    {
        Schema::table('timetable_entries', function (Blueprint $table) {
            $table->dropUnique(['day', 'period']);
            $table->dropColumn(['day', 'period', 'name', 'room']);
        });
    }
};