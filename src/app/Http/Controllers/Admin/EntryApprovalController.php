<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\TimetableEntry;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EntryApprovalController extends Controller
{
    // 履修申請管理画面
    public function index()
    {
        // 授業ごとの承認済み人数
        $courses = Course::withCount([
            'timetableEntries as approved_count' => function ($query) {
                $query->where('status', 'approved');
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
    public function approve(TimetableEntry $entry)
    {
        DB::transaction(function () use ($entry) {

            // 同時に承認されても定員を超えないように授業をロック
            $course = Course::whereKey($entry->course_id)
                ->lockForUpdate()
                ->firstOrFail();

            // 申請の最新状態を取得
            $entry = TimetableEntry::whereKey($entry->id)
                ->lockForUpdate()
                ->firstOrFail();

            // 申請中でなければ承認できない
            if ($entry->status !== 'pending') {
                throw ValidationException::withMessages([
                    'entry' => 'この申請はすでに処理されています。',
                ]);
            }

            // 承認済み人数を数える
            $approvedCount = TimetableEntry::where('course_id', $course->id)
                ->where('status', 'approved')
                ->count();

            // 定員に達していたら承認できない
            if ($approvedCount >= $course->capacity) {
                throw ValidationException::withMessages([
                    'entry' => 'この授業は定員に達しています。',
                ]);
            }

            // 承認する
            $entry->status = 'approved';
            $entry->decided_at = now();
            $entry->save();
        });

        return redirect()
            ->route('admin.entries')
            ->with('success', '履修申請を承認しました！');
    }

    // 履修申請を却下
    public function reject(TimetableEntry $entry)
    {
        DB::transaction(function () use ($entry) {

            $entry = TimetableEntry::whereKey($entry->id)
                ->lockForUpdate()
                ->firstOrFail();

            // 申請中でなければ却下できない
            if ($entry->status !== 'pending') {
                throw ValidationException::withMessages([
                    'entry' => 'この申請はすでに処理されています。',
                ]);
            }

            // 却下する
            $entry->status = 'rejected';
            $entry->decided_at = now();
            $entry->save();
        });

        return redirect()
            ->route('admin.entries')
            ->with('success', '履修申請を却下しました。');
    }
}
