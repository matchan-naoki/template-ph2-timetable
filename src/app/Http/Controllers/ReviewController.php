<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    // レビューを投稿する
    public function store(Request $request, Course $course)
    {
        // 入力内容をチェックする
        $validated = $request->validate([
            'rating' => 'required|integer|between:1,5',
            'comment' => 'required|string|max:1000',
        ]);

        // レビューをデータベースに保存する
        Review::create([
            'user_id' => $request->user()->id,
            'course_id' => $course->id,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'],
        ]);

        // 投稿後、授業の詳細ページに戻る
        return redirect()
            ->route('courses.show', $course->id)
            ->with('success', 'レビューを投稿しました！');
    }

    // レビューを削除する
    public function destroy(Request $request, Review $review)
    {
        // 自分が投稿したレビューか確認
        if ($review->user_id !== $request->user()->id) {
            abort(403);
        }

        // レビューを投稿した授業のIDを取得
        $courseId = $review->course_id;

        // レビューを削除
        $review->delete();

        // 授業詳細ページに戻る
        return redirect()
            ->route('courses.show', $courseId)
            ->with('success', 'レビューを削除しました！');
    }
}