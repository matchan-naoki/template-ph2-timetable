<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\User;
use App\Models\Course;
use App\Models\TimetableEntry;

class EnrollmentTest extends TestCase
{
    use DatabaseTransactions;

    // ① 未ログインでは履修申請できない
    public function test_guest_cannot_enroll(): void
    {
        $beforeCount = DB::table('timetable_entries')->count();

        $response = $this->post('/entries', [
            'course_id' => 2,
        ]);

        // ログイン画面に移動する
        $response->assertRedirect('/login');

        // 履修申請データが増えていない
        $this->assertEquals(
            $beforeCount,
            DB::table('timetable_entries')->count()
        );
    }

    // ② ログインした学生は履修申請できる
    public function test_logged_in_student_can_enroll(): void
    {
        // 申請前の件数を取得
        $beforeCount = DB::table('timetable_entries')->count();

        // テスト用ユーザーを作成
        $user = User::factory()->create();

        // テスト用授業を作成
        $course = new Course();
        $course->code = 'TEST101';
        $course->name = 'テスト用授業';
        $course->teacher = 'テスト先生';
        $course->day = 1;
        $course->period = 1;
        $course->room = '101';
        $course->credits = 2;
        $course->required = false;
        $course->save();

        // 作成したユーザーでログイン
        $this->actingAs($user);

        // 履修申請を送信
        $response = $this->post('/entries', [
            'course_id' => $course->id,
        ]);

        // 時間割ページに戻る
        $response->assertRedirect('/entries');

        // 申請中のデータが保存されている
        $this->assertDatabaseHas('timetable_entries', [
            'user_id' => $user->id,
            'course_id' => $course->id,
            'status' => 'pending',
        ]);

        // 履修申請がちょうど1件増えている
        $this->assertEquals(
            $beforeCount + 1,
            DB::table('timetable_entries')->count()
        );
    }

    // ③ 却下された授業には再申請できない
    public function test_rejected_course_cannot_be_applied_again(): void
    {
        // テスト用ユーザーを作成
        $user = User::factory()->create();

        // テスト用授業を作成
        $course = new Course();
        $course->code = 'TEST102';
        $course->name = '却下テスト用授業';
        $course->teacher = 'テスト先生';
        $course->day = 2;
        $course->period = 1;
        $course->room = '102';
        $course->credits = 2;
        $course->required = false;
        $course->save();

        // 却下済みの履修申請を作成
        $entry = new TimetableEntry();
        $entry->user_id = $user->id;
        $entry->course_id = $course->id;
        $entry->status = 'rejected';
        $entry->save();

        // 作成した学生としてログイン
        $this->actingAs($user);

        // 同じ授業に再申請する
        $response = $this->post('/entries', [
            'course_id' => $course->id,
        ]);

        // 再申請が拒否されたことを確認
        $response->assertSessionHasErrors('course_id');

        // 履修申請の件数が増えていないことを確認
        $this->assertDatabaseCount('timetable_entries', 1);
    }

    // ④ 自分の申請中の履修を取り消せる
    public function test_student_can_cancel_own_pending_enrollment(): void
    {
        // テスト用ユーザーを作成
        $user = User::factory()->create();

        // テスト用授業を作成
        $course = new Course();
        $course->code = 'TEST103';
        $course->name = '取消テスト用授業';
        $course->teacher = 'テスト先生';
        $course->day = 3;
        $course->period = 2;
        $course->room = '103';
        $course->credits = 2;
        $course->required = false;
        $course->save();

        // 申請中の履修データを作成
        $entry = new TimetableEntry();
        $entry->user_id = $user->id;
        $entry->course_id = $course->id;
        $entry->status = 'pending';
        $entry->save();

        // 作成した学生としてログイン
        $this->actingAs($user);

        // 自分の履修申請を取り消す
        $response = $this->delete('/entries/' . $entry->id);

        // 削除後、時間割ページに戻ることを確認
        $response->assertRedirect('/entries');

        // 履修申請がDBから削除されたことを確認
        $this->assertDatabaseMissing('timetable_entries', [
            'id' => $entry->id,
        ]);
    }

    // ⑤ 他人の履修申請は削除できない
    public function test_student_cannot_delete_another_students_enrollment(): void
    {
        // 2人の学生を作成
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        // テスト用授業を作成
        $course = new Course();
        $course->code = 'TEST104';
        $course->name = '他人の履修削除テスト用授業';
        $course->teacher = 'テスト先生';
        $course->day = 4;
        $course->period = 2;
        $course->room = '104';
        $course->credits = 2;
        $course->required = false;
        $course->save();

        // 学生Bの履修申請を作成
        $entry = new TimetableEntry();
        $entry->user_id = $userB->id;
        $entry->course_id = $course->id;
        $entry->status = 'pending';
        $entry->save();

        // 学生Aとしてログイン
        $this->actingAs($userA);

        // 学生Bの履修申請を削除しようとする
        $response = $this->delete('/entries/' . $entry->id);

        // 他人の履修申請は見つからない扱いになる
        $response->assertNotFound();

        // 学生Bの履修申請がDBに残っていることを確認
        $this->assertDatabaseHas('timetable_entries', [
            'id' => $entry->id,
            'user_id' => $userB->id,
            'course_id' => $course->id,
            'status' => 'pending',
        ]);
    }

    // ⑥ 管理者が申請中の履修を承認できる
    public function test_admin_can_approve_pending_enrollment(): void
    {
        // テスト用学生を作成
        $user = User::factory()->create();

        // テスト用授業を作成
        $course = new Course();
        $course->code = 'TEST105';
        $course->name = '承認テスト用授業';
        $course->teacher = 'テスト先生';
        $course->day = 5;
        $course->period = 2;
        $course->room = '105';
        $course->credits = 2;
        $course->required = false;
        $course->capacity = 30;
        $course->save();

        // 承認待ちの履修申請を作成
        $entry = new TimetableEntry();
        $entry->user_id = $user->id;
        $entry->course_id = $course->id;
        $entry->status = 'pending';
        $entry->save();

        // テスト用管理者を作成
        $admin = User::factory()->create();

        // 管理者権限を付与
        $admin->is_admin = true;
        $admin->save();

        // 管理者としてログイン
        $this->actingAs($admin);

        // 管理者として履修申請を承認
        $response = $this->post(
            '/admin/entries/' . $entry->id . '/approve'
        );

        // 承認後、管理画面に戻ることを確認
        $response->assertRedirect(route('admin.entries'));

        // 履修申請がapprovedになっていることを確認
        $this->assertDatabaseHas('timetable_entries', [
            'id' => $entry->id,
            'user_id' => $user->id,
            'course_id' => $course->id,
            'status' => 'approved',
        ]);

        // 承認日時が保存されていることを確認
        $entry->refresh();
        $this->assertNotNull($entry->decided_at);
    }
}


