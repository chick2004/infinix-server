<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommentMedia extends Model
{
    protected $table = 'comment_medias';

    protected $fillable = [
        'comment_id',
        'path',
        'type',
    ];

    public function comment()
    {
        return $this->belongsTo(Comment::class);
    }
}
