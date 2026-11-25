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
        // ========================================
        // Week13：必修授業の自動登録・自動承認
        // ========================================

        // 学生の場合のみ必修授業を自動登録する
        if (!auth()->user()->is_admin) {
            $requiredCourses = Course::where('required', true)->get();

            foreach ($requiredCourses as $course) {
                $entry = auth()->user()
                    ->entries()
                    ->firstOrCreate(
                        ['course_id' => $course->id],
                        [
                            'status' => 'approved',
                            'decided_at' => now(),
                        ]
                    );

                // 既存の申請中・却下も承認済みに変更
                if ($entry->status !== 'approved') {
                    $entry->status = 'approved';
                    $entry->decided_at = now();
                    $entry->save();
                }
            }
        }

        // ========================================
        // 自分の履修情報を取得
        // ========================================

        $entries = TimetableEntry::with([
            'course' => function ($query) {
                // 管理者を除いた承認済み履修人数
                $query->withCount([
                    'timetableEntries' => function ($query) {
                        $query->where('status', 'approved')
                            ->whereHas('user', function ($userQuery) {
                                $userQuery->where('is_admin', false);
                            });
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
        // Week13：単位チェック
        // ========================================

        // 大学が定めた1学期の単位上限
        $creditLimit = 24;

        // 承認済み授業の単位だけ合計
        $totalCredits = $entries
            ->where('status', 'approved')
            ->sum(function ($entry) {
                return $entry->course->credits;
            });

        // 単位上限を超えているか確認
        $isOverLimit = $totalCredits > $creditLimit;

        // ========================================
        // Week13：必修授業の履修状況チェック
        // ========================================

        // 必修授業をすべて取得
        $requiredCourses = Course::where('required', true)
            ->orderBy('day')
            ->orderBy('period')
            ->get();

        // 自分の履修情報を授業IDごとに整理
        $myEntries = $entries->keyBy('course_id');

        // 必修授業ごとの履修状況を確認
        $requiredCourseStatuses = $requiredCourses->map(function ($course) use ($myEntries) {
            $entry = $myEntries->get($course->id);

            return [
                'course' => $course,
                'status' => $entry ? $entry->status : 'unregistered',
            ];
        });

        // ========================================
        // 授業からのお知らせを取得
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
            'totalCredits' => $totalCredits,
            'creditLimit' => $creditLimit,
            'isOverLimit' => $isOverLimit,
            'requiredCourseStatuses' => $requiredCourseStatuses,
        ]);
    }

    // ========================================
    // 授業登録画面（プルダウン形式）
    // ========================================

    public function create()
    {
        // 管理者は履修登録画面にアクセスできない
        if (auth()->user()->is_admin) {
            return redirect('/entries')
                ->withErrors([
                    'entry' => '管理者は履修登録できません。',
                ]);
        }

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

    // ========================================
    // 授業登録
    // ========================================

    public function store(Request $request)
    {
        // 管理者は履修申請できない
        if ($request->user()->is_admin) {
            return redirect('/entries')
                ->withErrors([
                    'course_id' => '管理者は履修申請できません。',
                ]);
        }

        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
        ]);

        $course = Course::findOrFail($validated['course_id']);
        // 抽選が完了した授業には申請できない
if ($course->lottery_completed_at !== null) {
    return back()->withErrors([
        'course_id' => 'この授業はすでに抽選が完了しているため、履修申請できません。',
    ])->withInput();
}

        // 履修申請の締切を過ぎていたら申請できない
        if (
            $course->application_deadline !== null &&
            now()->greaterThanOrEqualTo($course->application_deadline)
        ) {
            return back()->withErrors([
                'course_id' => 'この授業は履修申請の締切を過ぎています。',
            ])->withInput();
        }

        // 同じ曜日・時限に履修情報があるか確認
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

    // ========================================
    // 授業を1件削除
    // ========================================

    public function destroy(Request $request, $id)
    {
        // 自分の履修情報だけ取得
        $entry = $request->user()
            ->entries()
            ->with('course')
            ->findOrFail($id);

        // 必修授業は削除できない
        if ($entry->course->required) {
            return redirect('/entries')
                ->withErrors([
                    'entry' => '必修授業は削除できません。',
                ]);
        }

        // 選択授業だけ削除
        $entry->delete();

        return redirect('/entries')
            ->with('success', '授業を削除しました。');
    }

    // ========================================
    // 選択授業をすべて削除
    // ========================================

    public function destroyAll(Request $request)
    {
        // 必修以外の授業だけ削除
        $request->user()
            ->entries()
            ->whereHas('course', function ($query) {
                $query->where('required', false);
            })
            ->delete();

        return redirect('/entries')
            ->with('success', '選択授業をすべて削除しました。');
    }

    // ========================================
    // 授業一覧から選ぶ画面
    // ========================================

    public function courseList(Request $request)
    {
        // 管理者は履修登録画面にアクセスできない
        if ($request->user()->is_admin) {
            return redirect('/entries')
                ->withErrors([
                    'entry' => '管理者は履修登録できません。',
                ]);
        }

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
