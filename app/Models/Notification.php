<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'notifications';
    protected $fillable = [
        'receiver_id',
        'trigger_id',
        'reference_id',
        'type',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function trigger()
    {
        return $this->belongsTo(User::class, 'trigger_id');
    }
}
