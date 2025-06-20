<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    protected $fillable = ['is_group', 'name', 'image'];

    public function users()
    {
        return $this->belongsToMany(User::class, 'conversation_users');
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }
}
