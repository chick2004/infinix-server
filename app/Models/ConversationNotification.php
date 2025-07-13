<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConversationNotification extends Model
{
    protected $table = 'conversation_notifications';
    protected $fillable = [
        'receiver_id',
        'conversation_id',
        'type',
        'is_read',
    ];
    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
    public function conversation()
    {
        return $this->belongsTo(Conversation::class, 'conversation_id');
    }
}
