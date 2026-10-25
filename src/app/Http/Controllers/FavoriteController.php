<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Favorite;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    // お気に入り登録・解除
    public function toggle(Request $request, Course $course)
    {
        // ログイン中のユーザーのお気に入りを確認
        $favorite = Favorite::where('user_id', $request->user()->id)
            ->where('course_id', $course->id)
            ->first();

        if ($favorite) {
            // 登録済みなら解除
            $favorite->delete();

            $message = 'お気に入りを解除しました';
        } else {
            // 未登録なら追加
            Favorite::firstOrCreate([
                'user_id' => $request->user()->id,
                'course_id' => $course->id,
            ]);

            $message = 'お気に入りに登録しました';
        }

        return back()->with('success', $message);
    }
}