<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Course;
use App\Models\TimetableEntry;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class Week13CreditTest extends TestCase
{
    use DatabaseTransactions;

    // テスト用授業を作成
    private function createCourse(
        string $code,
        int $credits = 2,
        bool $required = false
    ): Course {
        $course = new Course();
        $course->code = $code;
        $course->name = 'テスト授業 ' . $code;
        $course->teacher = 'テスト先生';
        $course->day = 1;
        $course->period = 1;
        $course->room = '101';
        $course->credits = $credits;
        $course->required = $required;
        $course->capacity = 100;
        $course->save();

        return $course;
    }

    // テスト用履修データを作成
    private function createEntry(
        User $user,
        Course $course,
        string $status = 'approved'
    ): TimetableEntry {
        $entry = new TimetableEntry();
        $entry->user_id = $user->id;
        $entry->course_id = $course->id;
        $entry->status = $status;
        $entry->save();

        return $entry;
    }

    // ① 必修授業が自動登録・自動承認される
    public function test_required_course_is_automatically_approved(): void
    {
        $user = User::factory()->create();
        $course = $this->createCourse('W13REQ01', 2, true);

        $this->actingAs($user)->get('/entries')->assertOk();

        $this->assertDatabaseHas('timetable_entries', [
            'user_id' => $user->id,
            'course_id' => $course->id,
            'status' => 'approved',
        ]);
    }

    // ② 必修授業は重複登録されない
    public function test_required_course_is_not_duplicated(): void
    {
        $user = User::factory()->create();
        $course = $this->createCourse('W13REQ02', 2, true);

        $this->actingAs($user)->get('/entries');
        $this->actingAs($user)->get('/entries');

        $count = TimetableEntry::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->count();

        $this->assertEquals(1, $count);
    }

    // ③ 必修授業は個別削除できない
    public function test_required_course_cannot_be_deleted(): void
    {
        $user = User::factory()->create();
        $course = $this->createCourse('W13REQ03', 2, true);
        $entry = $this->createEntry($user, $course);

        $response = $this->actingAs($user)
            ->delete('/entries/' . $entry->id);

        $response->assertRedirect('/entries');

        $this->assertDatabaseHas('timetable_entries', [
            'id' => $entry->id,
            'user_id' => $user->id,
            'course_id' => $course->id,
        ]);
    }

    // ④ 全削除でも必修授業は残る
    public function test_bulk_delete_preserves_required_courses(): void
    {
        $user = User::factory()->create();

        $required = $this->createCourse('W13REQ04', 2, true);
        $elective = $this->createCourse('W13ELE04', 2, false);

        $requiredEntry = $this->createEntry($user, $required);
        $electiveEntry = $this->createEntry($user, $elective);

        $this->actingAs($user)->delete('/entries');

        $this->assertDatabaseHas('timetable_entries', [
            'id' => $requiredEntry->id,
        ]);

        $this->assertDatabaseMissing('timetable_entries', [
            'id' => $electiveEntry->id,
        ]);
    }

    // ⑤ 承認済みの授業だけ単位に加算される

    // ⑤ 承認済みの授業だけ単位に加算される
    public function test_only_approved_credits_are_counted(): void
    {
        $user = User::factory()->create();

        // テスト用DBに既にある必修授業の単位合計
        $requiredCredits = Course::where('required', true)
            ->sum('credits');

        $approved = $this->createCourse('W13APP05', 4);
        $pending = $this->createCourse('W13PEN05', 3);
        $rejected = $this->createCourse('W13REJ05', 2);

        $this->createEntry($user, $approved, 'approved');
        $this->createEntry($user, $pending, 'pending');
        $this->createEntry($user, $rejected, 'rejected');

        $response = $this->actingAs($user)->get('/entries');

        $response->assertOk();

        // 既存の必修授業 + 承認済み4単位のみ
        $response->assertViewHas(
            'totalCredits',
            $requiredCredits + 4
        );
    }


    // ⑥ 24単位を超えると警告判定になる
    public function test_credit_limit_warning(): void
    {
        $user = User::factory()->create();

        $course = $this->createCourse('W13LIM06', 25);
        $this->createEntry($user, $course);

        $response = $this->actingAs($user)->get('/entries');

        $response->assertViewHas('creditLimit', 24);
        $response->assertViewHas('isOverLimit', true);
    }

    // ⑦ 他の学生の履修単位は含まれない

    // ⑦ 他の学生の履修単位は含まれない
    public function test_other_students_credits_are_not_counted(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        // 既存の必修授業の単位合計
        $requiredCredits = Course::where('required', true)
            ->sum('credits');

        $course = $this->createCourse('W13USR07', 4);

        // 学生Bだけが4単位の授業を履修
        $this->createEntry($userB, $course);

        // 学生Aで時間割を表示
        $response = $this->actingAs($userA)->get('/entries');

        $response->assertOk();

        // 学生Bの4単位は含まれない
        $response->assertViewHas(
            'totalCredits',
            $requiredCredits
        );
    }


    // ⑧ 必修授業が申請中でも自動承認される
    public function test_pending_required_course_becomes_approved(): void
    {
        $user = User::factory()->create();
        $course = $this->createCourse('W13REQ08', 2, true);

        $entry = $this->createEntry($user, $course, 'pending');

        $this->actingAs($user)->get('/entries');

        $this->assertDatabaseHas('timetable_entries', [
            'id' => $entry->id,
            'status' => 'approved',
        ]);
    }
}
