<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    // 保存できるカラム
protected $fillable = [
    'course_id',
    'user_id',
    'title',
    'body',
    'submission_token',
];

    // お知らせが属する授業
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    // お知らせを投稿したユーザー
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
