<?php

use App\Http\Controllers\TimetableEntryController;
use App\Http\Controllers\TimetableController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/entries');
});

// 時間割一覧
Route::get('/timetable', [TimetableController::class, 'index']);

// 登録した授業の一覧
Route::get('/entries', [TimetableEntryController::class, 'index']);

// 授業登録画面
Route::get('/entries/create', [TimetableEntryController::class, 'create']);

// 授業登録
Route::post('/entries', [TimetableEntryController::class, 'store']);

// 授業を1件削除
Route::delete('/entries/{id}', [TimetableEntryController::class, 'destroy']);

// 授業を全件削除
Route::delete('/entries', [TimetableEntryController::class, 'destroyAll']);

Route::get('/entries/courses', [TimetableEntryController::class, 'courseList']);
