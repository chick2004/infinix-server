<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostMedia extends Model
{
    protected $table = 'post_medias';

    protected $fillable = [
        'post_id',
        'path',
        'type',
    ];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
