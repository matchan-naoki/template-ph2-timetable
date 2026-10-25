<?php

namespace App\Http\Controllers;

use App\Models\Course;

class CourseController extends Controller
{
    public function show(Course $course)
    {
        // この授業に投稿されたレビューを取得
        $reviews = $course->reviews()
            ->with('user')
            ->latest('id')
            ->get();

        // 授業詳細ページを表示
        return view('courses.show', [
            'course' => $course,
            'reviews' => $reviews,
        ]);
    }
}
