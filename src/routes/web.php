<?php
use App\Http\Controllers\TimetableEntryController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
use App\Http\Controllers\TimetableController;

Route::get('/timetable', [TimetableController::class, 'index']);
Route::get('/entries', [TimetableEntryController::class, 'index']);
Route::get('/entries/create', [TimetableEntryController::class, 'create']);
Route::post('/entries', [TimetableEntryController::class, 'store']);
Route::get('/entries/{id}/edit', [TimetableEntryController::class, 'edit']);
Route::put('/entries/{id}', [TimetableEntryController::class, 'update']);
Route::delete('/entries/{id}', [TimetableEntryController::class, 'destroy']);
Route::delete('/entries', [TimetableEntryController::class, 'destroyAll']);