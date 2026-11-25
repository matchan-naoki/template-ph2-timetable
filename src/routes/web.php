<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TimetableEntryController;
use App\Http\Controllers\TimetableController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\Admin\EntryApprovalController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\RoomReservationController;
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

    // 授業詳細ページ
    Route::get('/courses/{course}', [CourseController::class, 'show'])
        ->name('courses.show');

    // レビュー投稿
    Route::post('/courses/{course}/reviews', [ReviewController::class, 'store'])
        ->name('reviews.store');

    // レビュー削除
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])
        ->name('reviews.destroy');

    // お気に入り登録・解除
    Route::post('/courses/{course}/favorite', [FavoriteController::class, 'toggle'])
        ->name('courses.favorite');

    // 教室予約（一般学生も利用可能）
    Route::get('/room-reservations', [
        RoomReservationController::class, 'index'
    ])->name('room-reservations.index');

    Route::post('/room-reservations', [
        RoomReservationController::class, 'store'
    ])->name('room-reservations.store');


// 自分の教室予約を削除
Route::delete('/room-reservations/{reservation}', [
    RoomReservationController::class, 'destroy'
])->name('room-reservations.destroy');


    // 管理者専用
    Route::middleware('can:access-admin')->group(function () {

        // 履修申請一覧
        Route::get('/admin/entries', [EntryApprovalController::class, 'index'])
            ->name('admin.entries');

        // 履修申請を承認
        Route::post('/admin/entries/{entry}/approve', [EntryApprovalController::class, 'approve'])
            ->name('admin.entries.approve');

        // 履修申請を却下
        Route::post('/admin/entries/{entry}/reject', [EntryApprovalController::class, 'reject'])
            ->name('admin.entries.reject');

            // 授業ごとの履修申請締切を設定
Route::post('/admin/courses/{course}/deadline', [
    EntryApprovalController::class, 'updateDeadline'
])->name('admin.courses.deadline');

        // お知らせの投稿内容を確認
        Route::post('/courses/{course}/announcements/confirm', [
            AnnouncementController::class, 'confirm'
        ])->name('announcements.confirm');

        // お知らせの投稿を確定
        Route::post('/courses/{course}/announcements', [
            AnnouncementController::class, 'store'
        ])->name('announcements.store');

        Route::post('/admin/courses/{course}/lottery', [EntryApprovalController::class, 'lottery'])
    ->name('admin.courses.lottery');

    });

});
