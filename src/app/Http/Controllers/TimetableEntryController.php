<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\TimetableEntry;
use Illuminate\Http\Request;

class TimetableEntryController extends Controller
{
    // 授業一覧
    public function index()
    {
        $entries = TimetableEntry::with('course')
            ->join('courses', 'timetable_entries.course_id', '=', 'courses.id')
            ->orderBy('courses.day')
            ->orderBy('courses.period')
            ->select('timetable_entries.*')
            ->get();

        return view('entries.index', [
            'entries' => $entries
        ]);
    }

public function create()
{
    $courses = Course::orderBy('day')
        ->orderBy('period')
        ->get();

    $registeredCourseIds = TimetableEntry::pluck('course_id')->toArray();

    return view('entries.create', [
        'courses' => $courses,
        'registeredCourseIds' => $registeredCourseIds,
    ]);
}

    // 授業登録
    public function store(Request $request)
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
        ]);

        $course = Course::findOrFail($validated['course_id']);

        // 同じ曜日・時限の授業が登録されているか確認
        $exists = TimetableEntry::whereHas('course', function ($query) use ($course) {
            $query->where('day', $course->day)
                  ->where('period', $course->period);
        })->exists();

        // 重複していたら登録しない
        if ($exists) {
            return back()->withErrors([
                'course_id' => 'この曜日・時限にはすでに授業が登録されています。',
            ])->withInput();
        }

        // 重複していなければ登録
        TimetableEntry::create([
            'course_id' => $course->id,
        ]);

        return redirect('/entries')->with('success', '授業を登録しました！');
    }

    // 授業を1件削除
    public function destroy($id)
    {
        $entry = TimetableEntry::findOrFail($id);
        $entry->delete();

        return redirect('/entries');
    }

    // 登録した授業を全件削除
    public function destroyAll()
    {
        TimetableEntry::query()->delete();

        return redirect('/entries');
    }

    public function courseList()
{
    $courses = Course::orderBy('day')
        ->orderBy('period')
        ->get();

    $registeredCourseIds = TimetableEntry::pluck('course_id')->toArray();

    return view('entries.course-list', [
        'courses' => $courses,
        'registeredCourseIds' => $registeredCourseIds,
    ]);
}
}
