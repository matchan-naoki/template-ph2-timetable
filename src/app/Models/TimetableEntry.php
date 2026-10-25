<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimetableEntry extends Model
{
    protected $fillable = [
        'day',
        'period',
        'course_id',
    ];

    // この時間割に登録されている授業を取得
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    // この時間割を登録したユーザーを取得
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}