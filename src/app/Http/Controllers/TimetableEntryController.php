<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\TimetableEntry;
use App\Models\Tag;
use App\Models\Announcement;
use Illuminate\Http\Request;

class TimetableEntryController extends Controller
{
    // 自分の時間割一覧
    public function index()
    {
        $entries = TimetableEntry::with([
            'course' => function ($query) {
                // 授業ごとの承認済み履修人数を取得
                $query->withCount([
                    'timetableEntries' => function ($query) {
                        $query->where('status', 'approved');
                    }
                ]);
            }
        ])
            ->where('user_id', auth()->id())
            ->join('courses', 'timetable_entries.course_id', '=', 'courses.id')
            ->orderBy('courses.day')
            ->orderBy('courses.period')
            ->select('timetable_entries.*')
            ->get();

        // ========================================
        // 追加：授業からのお知らせを取得
        // ========================================

        // 自分が履修承認された授業IDだけ取得
        $approvedCourseIds = auth()->user()
            ->entries()
            ->where('status', 'approved')
            ->pluck('course_id');

        // 承認済み授業の過去30日間のお知らせを取得
        $announcements = Announcement::whereIn(
            'course_id',
            $approvedCourseIds
        )
            ->where('created_at', '>=', now()->subDays(30))
            ->with(['course', 'user'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        // 時間割とお知らせをBladeに渡す
        return view('entries.index', [
            'entries' => $entries,
            'announcements' => $announcements,
        ]);
    }

    // 授業登録画面（プルダウン形式）
    public function create()
    {
        $courses = Course::orderBy('day')
            ->orderBy('period')
            ->get();

        // 自分が登録している授業だけを取得
        $registeredCourseIds = auth()->user()
            ->entries()
            ->whereIn('status', ['pending', 'approved'])
            ->pluck('course_id')
            ->toArray();

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

        $exists = $request->user()
            ->entries()
->whereIn('status', ['pending', 'approved', 'rejected'])
            ->whereHas('course', function ($query) use ($course) {
                $query->where('day', $course->day)
                    ->where('period', $course->period);
            })
            ->exists();

        // 重複していたら登録しない
        if ($exists) {
            return back()->withErrors([
                'course_id' => 'この曜日・時限にはすでに授業が登録されています。',
            ])->withInput();
        }

        // 授業を登録
        $request->user()->entries()->create([
            'course_id' => $course->id,
            'status' => 'pending',
        ]);

        return redirect('/entries')
            ->with('success', '履修申請を受け付けました！');
    }

    // 授業を1件削除
    public function destroy(Request $request, $id)
    {
        $entry = $request->user()
            ->entries()
            ->findOrFail($id);

        $entry->delete();

        return redirect('/entries');
    }

    // 登録した授業をすべて削除
    public function destroyAll(Request $request)
    {
        $request->user()->entries()->delete();

        return redirect('/entries');
    }

    // 授業一覧から選ぶ画面
    public function courseList(Request $request)
    {
        // 検索キーワード
        $q = $request->query('q', '');

        // 選択されたタグID
        $tagId = $request->query('tag');

        // タグ一覧を取得
        $tags = Tag::orderBy('id')->get();

        // 授業・タグ・お気に入り数を取得
        $query = Course::with('tags')
            ->withCount('favorites');

        // 授業名・担当教員名で検索
        if ($q !== '') {
            $query->where(function ($builder) use ($q) {
                $builder->where('name', 'LIKE', "%{$q}%")
                    ->orWhere('teacher', 'LIKE', "%{$q}%");
            });
        }

        // タグで絞り込み
        if ($tagId) {
            $query->whereHas('tags', function ($builder) use ($tagId) {
                $builder->where('tags.id', $tagId);
            });
        }

        // 曜日・時限順に並べる
        $courses = $query
            ->orderBy('day')
            ->orderBy('period')
            ->get();

        // 自分が登録済みの授業ID
        $registeredCourseIds = $request->user()
            ->entries()
            ->pluck('course_id')
            ->toArray();

        // 自分がお気に入り登録した授業ID
        $favoriteCourseIds = $request->user()
            ->favorites()
            ->pluck('course_id')
            ->toArray();

        return view('entries.course-list', [
            'courses' => $courses,
            'registeredCourseIds' => $registeredCourseIds,
            'favoriteCourseIds' => $favoriteCourseIds,
            'tags' => $tags,
            'tagId' => $tagId,
            'q' => $q,
        ]);
    }
}