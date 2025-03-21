<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostBookmark extends Model
{
    protected $table = 'post_bookmarks';

    protected $fillable = ['user_id', 'post_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
