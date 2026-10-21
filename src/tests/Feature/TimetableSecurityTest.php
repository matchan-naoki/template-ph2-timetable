<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TimetableSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
{
    parent::setUp();

    // テスト用SQLiteにcoursesテーブルを作成
    if (!Schema::hasTable('courses')) {
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
}

    public function test_other_users_cannot_delete_entries(): void
    {
        // AさんとBさんを作成
        $userA = User::factory()->create();
        $userB = User::factory()->create();

// テスト用の授業を作成
$courseId = DB::table('courses')->insertGetId([
    'code' => 'TEST101',
    'name' => 'テスト用授業',
    'teacher' => 'テスト教員',
    'day' => 1,
    'period' => 1,
    'room' => 'J101',
    'credits' => 2,
    'required' => false,
]);

        // Aさんの履修を登録
        $entryId = DB::table('timetable_entries')->insertGetId([
            'user_id' => $userA->id,
            'course_id' => $courseId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // BさんとしてAさんの履修を削除しようとする
        $this->actingAs($userB)
            ->delete("/entries/{$entryId}")
            ->assertNotFound();

        // Aさんの履修が残っていることを確認
        $this->assertDatabaseHas('timetable_entries', [
            'id' => $entryId,
            'user_id' => $userA->id,
        ]);
    }
}

