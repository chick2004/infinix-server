<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $table = 'messages';
    
    protected $fillable = [
        'conversation_id',
        'user_id',
        'reply_to_message_id',
        'is_edited',
        'is_deleted',
        'is_recalled',
        'content',
    ];

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function replyToMessage()
    {
        return $this->belongsTo(Message::class, 'reply_to_message_id');
    }

    public function medias()
    {
        return $this->hasMany(MessageMedia::class);
    }
}
