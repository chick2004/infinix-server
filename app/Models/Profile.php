<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    protected $table = 'profiles';

    protected $fillable = [
        'user_id',
        'display_name',
        'profile_photo',
        'cover_photo',
        'bio',
        'date_of_birth',
        'gender',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
