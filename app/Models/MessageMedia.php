<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MessageMedia extends Model
{
    protected $table = 'message_medias';
    
    protected $fillable = ['message_id', 'path', 'type'];

    public function message()
    {
        return $this->belongsTo(Message::class);
    }
}
