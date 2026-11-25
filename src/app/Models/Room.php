<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    protected $table = 'rooms';

    // 教室コードを主キーにする
    protected $primaryKey = 'code';

    // 主キーは文字列
    protected $keyType = 'string';

    // 自動採番しない
    public $incrementing = false;

    // roomsテーブルにはcreated_atなどがない
    public $timestamps = false;

    protected $fillable = [
        'code',
        'building',
        'floor',
        'capacity',
    ];

    // この教室の予約一覧
    public function reservations(): HasMany
    {
        return $this->hasMany(
            RoomReservation::class,
            'room_code',
            'code'
        );
    }
}
