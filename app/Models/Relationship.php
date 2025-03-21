<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Relationship extends Model
{
    protected $table = 'relationships';

    protected $fillable = ['user_id', 'related_user_id', 'type'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function related_user()
    {
        return $this->belongsTo(User::class, 'related_user_id');
    }
}
