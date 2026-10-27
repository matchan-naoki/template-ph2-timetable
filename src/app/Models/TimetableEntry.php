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
        'status',
        'decided_at',
    ];

    // 登録されている授業
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    // 申請したユーザー
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // 承認・却下日時を日付として扱う
    protected function casts(): array
    {
        return [
            'decided_at' => 'datetime',
        ];
    }
}