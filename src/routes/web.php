<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TimetableEntryController;
use App\Http\Controllers\TimetableController;
use Illuminate\Support\Facades\Route;

// 最初の画面
Route::get('/', function () {
    return redirect('/entries');
});

// 新規登録
Route::get('/register', [AuthController::class, 'showRegister']);
Route::post('/register', [AuthController::class, 'register']);

// ログイン
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

// ログアウト
Route::post('/logout', [AuthController::class, 'logout']);

// ログインしている人だけアクセスできる
Route::middleware('auth')->group(function () {

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

    // 授業一覧から選択
    Route::get('/entries/courses', [TimetableEntryController::class, 'courseList']);

});
