<?php

namespace App\Http\Controllers;

use App\Mail\AnnouncementMail;
use App\Models\Announcement;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Database\UniqueConstraintViolationException;

class AnnouncementController extends Controller
{
    // お知らせの投稿内容を確認する
    public function confirm(Request $request, Course $course)
    {
        Gate::authorize('access-admin');

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:50'],
            'body' => ['required', 'string', 'max:500'],
            'submission_token' => ['required', 'string', 'size:40'],
        ], [
            'title.required' => 'タイトルを入力してください',
            'title.max' => 'タイトルは50文字以内で入力してください',
            'body.required' => '本文を入力してください',
            'body.max' => '本文は500文字以内で入力してください',
        ]);

        $token = $validated['submission_token'];

        // すでに投稿されたトークンなら確認画面に進ませない
        if (Announcement::where('submission_token', $token)->exists()) {
            return redirect()
                ->route('courses.show', $course)
                ->withErrors([
                    'announcement' => 'このお知らせはすでに投稿されています',
                ]);
        }

        // 下書きをセッションに保存
        $request->session()->put('announcement_draft', [
            'token' => $token,
            'course_id' => $course->id,
            'title' => $validated['title'],
            'body' => $validated['body'],
        ]);

        return view('announcements.confirm', [
            'course' => $course,
            'title' => $validated['title'],
            'body' => $validated['body'],
            'token' => $token,
        ]);
    }

    // お知らせを投稿する
    public function store(Request $request, Course $course)
    {
        Gate::authorize('access-admin');

        $validated = $request->validate([
            'token' => ['required', 'string', 'size:40'],
        ]);

        $draft = $request->session()->get('announcement_draft');

        // 下書きが存在しない、またはトークンが一致しない場合
        if (
            !$draft ||
            !hash_equals($draft['token'], $validated['token']) ||
            (int) $draft['course_id'] !== (int) $course->id
        ) {
            return redirect()
                ->route('courses.show', $course)
                ->withErrors([
                    'announcement' => '投稿内容を確認し直してください',
                ]);
        }

        // すでに投稿されている場合は拒否
        if (Announcement::where('submission_token', $validated['token'])->exists()) {
            $request->session()->forget('announcement_draft');

            return redirect()
                ->route('courses.show', $course)
                ->withErrors([
                    'announcement' => 'このお知らせはすでに投稿されています',
                ]);
        }

        // データベースにも二重投稿防止を設定
        try {
            $announcement = $course->announcements()->create([
                'user_id' => $request->user()->id,
                'title' => $draft['title'],
                'body' => $draft['body'],
                'submission_token' => $validated['token'],
            ]);
        } catch (UniqueConstraintViolationException $e) {
            $request->session()->forget('announcement_draft');

            return redirect()
                ->route('courses.show', $course)
                ->withErrors([
                    'announcement' => 'このお知らせはすでに投稿されています',
                ]);
        }

        // 投稿完了後、下書きを削除
        $request->session()->forget('announcement_draft');

        // メール本文に必要な情報を取得
        $announcement->load(['course', 'user']);

        // 承認済み学生を取得
        $students = $course->timetableEntries()
            ->where('status', 'approved')
            ->with('user')
            ->get()
            ->pluck('user')
            ->filter()
            ->unique('id');

        // 学生一人ひとりにメール送信
        foreach ($students as $student) {
            Mail::to($student->email)->send(
                new AnnouncementMail($announcement, $student->name)
            );
        }

        return redirect()
            ->route('courses.show', $course)
            ->with('success', 'お知らせを投稿しました');
    }
}