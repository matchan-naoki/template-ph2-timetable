<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Course;
use App\Models\TimetableEntry;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class CourseLotteryTest extends TestCase
{
    use DatabaseTransactions;

    // テスト用の授業を作成
    private function createCourse(
        int $capacity = 30,
        bool $required = false,
        ?string $deadline = null
    ): Course {
        $course = new Course();
        $course->code = 'LOT' . uniqid();
        $course->name = '抽選テスト用授業';
        $course->teacher = 'テスト先生';
        $course->day = 1;
        $course->period = 1;
        $course->room = '101';
        $course->credits = 2;
        $course->required = $required;
        $course->capacity = $capacity;
        $course->application_deadline = $deadline;
        $course->save();

        return $course;
    }

    // 管理者を作成
    private function createAdmin(): User
    {
        $admin = User::factory()->create();
        $admin->is_admin = true;
        $admin->save();

        return $admin;
    }

    // 指定した人数の申請を作成
    private function createApplications(Course $course, int $count): void
    {
        for ($i = 0; $i < $count; $i++) {
            $user = User::factory()->create();
            $user->is_admin = false;
            $user->save();

            $entry = new TimetableEntry();
            $entry->user_id = $user->id;
            $entry->course_id = $course->id;
            $entry->status = 'pending';
            $entry->save();
        }
    }

    // ① 40人申請・定員30人なら30人当選、10人落選
    public function test_lottery_approves_30_and_rejects_10(): void
    {
        $course = $this->createCourse(
            30,
            false,
            now()->subDay()->toDateTimeString()
        );

        $this->createApplications($course, 40);

        $this->actingAs($this->createAdmin())
            ->post(route('admin.courses.lottery', $course))
            ->assertRedirect(route('admin.entries'));

        $this->assertEquals(
            30,
            TimetableEntry::where('course_id', $course->id)
                ->where('status', 'approved')
                ->count()
        );

        $this->assertEquals(
            10,
            TimetableEntry::where('course_id', $course->id)
                ->where('status', 'rejected')
                ->count()
        );

        $this->assertEquals(
            0,
            TimetableEntry::where('course_id', $course->id)
                ->where('status', 'pending')
                ->count()
        );

        $this->assertNotNull($course->fresh()->lottery_completed_at);
    }

    // ② 申請者が定員以下なら全員当選
    public function test_everyone_wins_when_under_capacity(): void
    {
        $course = $this->createCourse(
            30,
            false,
            now()->subDay()->toDateTimeString()
        );

        $this->createApplications($course, 12);

        $this->actingAs($this->createAdmin())
            ->post(route('admin.courses.lottery', $course))
            ->assertRedirect(route('admin.entries'));

        $this->assertEquals(
            12,
            TimetableEntry::where('course_id', $course->id)
                ->where('status', 'approved')
                ->count()
        );
    }

    // ③ 締切前は抽選できない
    public function test_cannot_run_lottery_before_deadline(): void
    {
        $course = $this->createCourse(
            30,
            false,
            now()->addDay()->toDateTimeString()
        );

        $this->createApplications($course, 1);

        $this->actingAs($this->createAdmin())
            ->post(route('admin.courses.lottery', $course))
            ->assertSessionHasErrors('lottery');

        $this->assertNull($course->fresh()->lottery_completed_at);
    }

    // ④ 必修授業は抽選できない
    public function test_required_course_cannot_be_lotteried(): void
    {
        $course = $this->createCourse(
            30,
            true,
            now()->subDay()->toDateTimeString()
        );

        $this->actingAs($this->createAdmin())
            ->post(route('admin.courses.lottery', $course))
            ->assertSessionHasErrors('lottery');

        $this->assertNull($course->fresh()->lottery_completed_at);
    }

    // ⑤ 同じ授業で2回抽選できない
    public function test_cannot_run_lottery_twice(): void
    {
        $course = $this->createCourse(
            30,
            false,
            now()->subDay()->toDateTimeString()
        );

        $this->createApplications($course, 2);

        $this->actingAs($this->createAdmin())
            ->post(route('admin.courses.lottery', $course))
            ->assertRedirect(route('admin.entries'));

        $this->post(route('admin.courses.lottery', $course))
            ->assertSessionHasErrors('lottery');
    }

    // ⑥ 一般学生は抽選できない
    public function test_student_cannot_run_lottery(): void
    {
        $course = $this->createCourse(
            30,
            false,
            now()->subDay()->toDateTimeString()
        );

        $student = User::factory()->create();
        $student->is_admin = false;
        $student->save();

        $this->actingAs($student)
            ->post(route('admin.courses.lottery', $course))
            ->assertForbidden();

        $this->assertNull($course->fresh()->lottery_completed_at);
    }

    // ⑦ 抽選後は新規申請できない
    public function test_student_cannot_apply_after_lottery(): void
    {
        $course = $this->createCourse(
            30,
            false,
            now()->subDay()->toDateTimeString()
        );

        $course->lottery_completed_at = now();
        $course->save();

        $student = User::factory()->create();
        $student->is_admin = false;
        $student->save();

        $this->actingAs($student)
            ->post('/entries', [
                'course_id' => $course->id,
            ])
            ->assertSessionHasErrors('course_id');

        $this->assertDatabaseMissing('timetable_entries', [
            'user_id' => $student->id,
            'course_id' => $course->id,
        ]);
    }

    // ⑧ 手動承認は禁止
    public function test_admin_cannot_manually_approve(): void
    {
        $course = $this->createCourse();

        $this->createApplications($course, 1);

        $entry = TimetableEntry::where('course_id', $course->id)->firstOrFail();

        $this->actingAs($this->createAdmin())
            ->post(route('admin.entries.approve', $entry))
            ->assertSessionHasErrors('entry');

        $this->assertEquals('pending', $entry->fresh()->status);
    }

    // ⑨ 手動却下は禁止
    public function test_admin_cannot_manually_reject(): void
    {
        $course = $this->createCourse();

        $this->createApplications($course, 1);

        $entry = TimetableEntry::where('course_id', $course->id)->firstOrFail();

        $this->actingAs($this->createAdmin())
            ->post(route('admin.entries.reject', $entry))
            ->assertSessionHasErrors('entry');

        $this->assertEquals('pending', $entry->fresh()->status);
    }
}
