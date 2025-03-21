<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MessageReact extends Model
{
    protected $table = 'message_reacts';
    
    protected $fillable = ['message_id', 'user_id', 'type'];

    public function message()
    {
        return $this->belongsTo(Message::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
