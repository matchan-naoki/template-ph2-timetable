<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Course extends Model
{
    public $timestamps = false;

    // 授業に登録されている時間割を取得する
    public function timetableEntries(): HasMany
    {
        return $this->hasMany(TimetableEntry::class);
    }

    // 授業に投稿されたレビューを取得する
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    // 授業に付いているタグを取得する
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    // 授業をお気に入り登録しているユーザーの情報を取得する
    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }
}