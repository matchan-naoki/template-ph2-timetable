<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Favorite extends Model
{
    protected $fillable = [
        'user_id',
        'course_id',
    ];

    // お気に入り登録したユーザー
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // お気に入り登録された授業
    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
