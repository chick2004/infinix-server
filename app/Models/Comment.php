<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $table = 'comments';

    protected $fillable = [
        'user_id',
        'post_id',
        'reply_to_comment_id',
        'content',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function reply_to_comment()
    {
        return $this->belongsTo(Comment::class, 'reply_to_comment_id');
    }

    public function medias()
    {
        return $this->hasMany(CommentMedia::class);
    }
}
