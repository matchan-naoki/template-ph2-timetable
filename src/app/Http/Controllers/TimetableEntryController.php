<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\TimetableEntry;
use Illuminate\Http\Request;

class TimetableEntryController extends Controller
{
public function index()
{
    $entries = TimetableEntry::with('course')
        ->where('user_id', auth()->id())
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

$exists = $request->user()->entries()
    ->whereHas('course', function ($query) use ($course) {
        $query->where('day', $course->day)
              ->where('period', $course->period);
    })->exists();

        // 重複していたら登録しない
        if ($exists) {
            return back()->withErrors([
                'course_id' => 'この曜日・時限にはすでに授業が登録されています。',
            ])->withInput();
        }

$request->user()->entries()->create([
    'course_id' => $course->id,
]);

        return redirect('/entries')->with('success', '授業を登録しました！');
    }

public function destroy(Request $request, $id)
{
    $entry = $request->user()->entries()->findOrFail($id);

    $entry->delete();

    return redirect('/entries');
}

public function destroyAll(Request $request)
{
    $request->user()->entries()->delete();

    return redirect('/entries');
}

public function courseList(Request $request)
{
    // 検索欄に入力された文字を取得
    $q = trim($request->query('q', ''));

    // 授業データを取得する準備
    $query = Course::query();

    // 検索文字が入力されている場合
    if ($q !== '') {
        $query->where(function ($query) use ($q) {
            $query->where('name', 'LIKE', "%{$q}%")
                  ->orWhere('teacher', 'LIKE', "%{$q}%");
        });
    }

    // 曜日・時限順に並べる
    $courses = $query->orderBy('day')
        ->orderBy('period')
        ->get();

    // ログイン中のユーザーが登録した授業
    $registeredCourseIds = $request->user()
        ->entries()
        ->pluck('course_id')
        ->toArray();

    // 授業一覧ページにデータを渡す
    return view('entries.course-list', [
        'courses' => $courses,
        'registeredCourseIds' => $registeredCourseIds,
        'q' => $q,
    ]);
}
}
