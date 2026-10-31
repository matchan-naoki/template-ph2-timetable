<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\TimetableEntry;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    // 授業一覧
    public function index(Request $request)
    {
        $q = $request->query('q', '');

        // ログイン中のユーザーの登録件数を取得
        $query = Course::withCount([
            'timetableEntries as entries_count' => function ($builder) use ($request) {
                $builder->where('user_id', $request->user()->id);
            }
        ]);

        // 授業名・担当教員名で検索
        if ($q !== '') {
            $query->where(function ($builder) use ($q) {
                $builder->where('name', 'LIKE', "%{$q}%")
                    ->orWhere('teacher', 'LIKE', "%{$q}%");
            });
        }

        $courses = $query
            ->orderBy('day')
            ->orderBy('period')
            ->orderBy('id')
            ->get();

        return view('courses.index', [
            'courses' => $courses,
            'q' => $q,
        ]);
    }

    // 授業詳細
    public function show(Course $course)
    {
        // レビューを取得
        $reviews = $course->reviews()
            ->with('user')
            ->latest('id')
            ->get();

        // 履修者を取得
        $students = $course->timetableEntries()
            ->with('user')
            ->get()
            ->pluck('user')
            ->filter()
            ->unique('id')
            ->values();

        // 履修人数
        $studentCount = $students->count();

        // タグを取得
        $course->load('tags');

        // ログイン中のユーザーが教務課かどうか
        $isAdmin = (bool) auth()->user()->is_admin;

        // この授業の履修が承認されているか確認
        $isApprovedStudent = TimetableEntry::where('course_id', $course->id)
            ->where('user_id', auth()->id())
            ->where('status', 'approved')
            ->exists();

        // お知らせを閲覧できるか
        $canViewAnnouncements = $isAdmin || $isApprovedStudent;

        // 過去30日間のお知らせを取得
        $announcements = $canViewAnnouncements
            ? $course->announcements()
                ->with('user')
                ->where('created_at', '>=', now()->subDays(30))
                ->latest()
                ->get()
            : collect();

        return view('courses.show', [
            'course' => $course,
            'reviews' => $reviews,
            'students' => $students,
            'studentCount' => $studentCount,
            'isAdmin' => $isAdmin,
            'canViewAnnouncements' => $canViewAnnouncements,
            'announcements' => $announcements,
        ]);
    }
}
