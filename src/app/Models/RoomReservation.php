<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomReservation extends Model
{
    protected $fillable = [
        'user_id',
        'room_code',
        'date',
        'period',
    ];

    protected $casts = [
        'date' => 'date',
        'period' => 'integer',
    ];

    // 予約した学生
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // 予約された教室
    public function room(): BelongsTo
    {
        return $this->belongsTo(
            Room::class,
            'room_code',
            'code'
        );
    }
}
