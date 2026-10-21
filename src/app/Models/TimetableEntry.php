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

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
}
