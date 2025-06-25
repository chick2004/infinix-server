<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use SoftDeletes;

    protected $table = 'posts';

    protected $fillable = [
        'user_id',
        'content',
        'visibility',
        'shared_post_id',
    ];

    public function getLikeCountAttribute()
    {
        return $this->likes()->count();
    }

    public function getCommentCountAttribute()
    {
        return $this->comments()->count();
    }

    public function getShareCountAttribute()
    {
        return $this->shares()->count();
    }
    

    protected function casts(): array
    {
        return [
            'is_shared' => 'boolean',
        ];
    } 

    public function medias()
    {
        return $this->hasMany(PostMedia::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function shared_post()
    {
        return $this->belongsTo(Post::class, 'shared_post_id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class, 'post_id');
    }

    public function likes()
    {
        return $this->hasMany(PostLike::class, 'post_id');
    }

    public function shares()
    {
        return $this->hasMany(PostShare::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'post_tags');
    }
}
