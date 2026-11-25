<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\TimetableEntry;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class EntryApprovalController extends Controller
{
    // 履修申請管理画面
    public function index()
    {
        // 授業ごとの承認済み人数
$courses = Course::withCount([
    'timetableEntries as approved_count' => function ($query) {
        $query->where('status', 'approved')
            ->whereHas('user', function ($userQuery) {
                $userQuery->where('is_admin', false);
            });
    }
])->orderBy('id')->get();

        // 承認待ちの申請
        $pending = TimetableEntry::where('status', 'pending')
            ->with(['user', 'course'])
            ->orderBy('id')
            ->get();

        return view('admin.entries', [
            'courses' => $courses,
            'pending' => $pending,
        ]);
    }

    // 履修申請を承認
// 手動承認は禁止（抽選方式を使用）
public function approve(TimetableEntry $entry)
{
    throw ValidationException::withMessages([
        'entry' => '履修申請は抽選によって承認されるため、手動承認はできません。',
    ]);
}


    // 履修申請を却下
// 手動却下は禁止（抽選方式を使用）
public function reject(TimetableEntry $entry)
{
    throw ValidationException::withMessages([
        'entry' => '履修申請は抽選によって決定されるため、手動却下はできません。',
    ]);
}

            // 授業ごとの履修申請締切を設定
public function updateDeadline(Request $request, Course $course)
{
    // 入力内容をチェック
    $validated = $request->validate([
        'application_deadline' => 'required|date|after:now',
    ], [
        'application_deadline.required' => '締切日時を入力してください。',
        'application_deadline.date' => '正しい日時を入力してください。',
        'application_deadline.after' => '未来の日時を設定してください。',
    ]);

    DB::transaction(function () use ($course, $validated) {

        // 同時更新を防ぐため授業をロック
        $course = Course::whereKey($course->id)
            ->lockForUpdate()
            ->firstOrFail();

        // 抽選済みなら変更できない
        if ($course->lottery_completed_at !== null) {
            throw ValidationException::withMessages([
                'application_deadline' => '抽選済みの授業は締切を変更できません。',
            ]);
        }

        // 締切日時を保存
        $course->application_deadline = $validated['application_deadline'];
        $course->save();
    });

    return redirect()
        ->route('admin.entries')
        ->with('success', '履修申請の締切日時を設定しました！');
}
// 履修申請の抽選を実行
public function lottery(Course $course)
{
    DB::transaction(function () use ($course) {

        // 同時実行を防ぐため授業をロック
        $course = Course::whereKey($course->id)
            ->lockForUpdate()
            ->firstOrFail();

        // 必修授業は抽選しない
        if ($course->required) {
            throw ValidationException::withMessages([
                'lottery' => '必修授業は抽選対象外です。',
            ]);
        }

        // 締切が設定されていない場合
        if ($course->application_deadline === null) {
            throw ValidationException::withMessages([
                'lottery' => '履修申請の締切が設定されていません。',
            ]);
        }

        // 締切前は抽選できない
        if (now()->lessThan($course->application_deadline)) {
            throw ValidationException::withMessages([
                'lottery' => '履修申請の締切前です。',
            ]);
        }

        // 二重抽選を防止
        if ($course->lottery_completed_at !== null) {
            throw ValidationException::withMessages([
                'lottery' => 'この授業はすでに抽選済みです。',
            ]);
        }

        // 申請中の学生だけ取得
        $entries = TimetableEntry::where('course_id', $course->id)
            ->where('status', 'pending')
            ->whereHas('user', function ($query) {
                $query->where('is_admin', false);
            })
            ->lockForUpdate()
            ->get();

        // 学生の承認済み人数を取得
        $approvedCount = TimetableEntry::where('course_id', $course->id)
            ->where('status', 'approved')
            ->whereHas('user', function ($query) {
                $query->where('is_admin', false);
            })
            ->count();

        // 残りの定員枠
        $remainingCapacity = max(0, $course->capacity - $approvedCount);

        // 申請者をランダムに並び替える
        $shuffledEntries = $entries->shuffle();

        foreach ($shuffledEntries as $index => $entry) {
            // 空き枠以内なら当選、それ以外は落選
            $entry->status = $index < $remainingCapacity
                ? 'approved'
                : 'rejected';

            $entry->decided_at = now();
            $entry->save();
        }

        // 抽選完了日時を記録
        $course->lottery_completed_at = now();
        $course->save();
    });

    return redirect()
        ->route('admin.entries')
        ->with('success', '履修抽選を実行しました！');
}
    }
