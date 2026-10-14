<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class TimetableController extends Controller
{
    public function index()
    {
        // 区画1：月曜日の授業（時限の早い順）
        $mondayCourses = DB::select('
            SELECT period, name, teacher, room
            FROM courses
            WHERE day = 1
            ORDER BY period ASC
        ');

        // 区画2：必修科目（科目コードの順）
        $requiredCourses = DB::select('
            SELECT name, credits
            FROM courses
            WHERE required = true
            ORDER BY code ASC
        ');

        // 取得したデータをビューに渡す
        return view('timetable', [
            'mondayCourses' => $mondayCourses,
            'requiredCourses' => $requiredCourses,
        ]);
    }
}
