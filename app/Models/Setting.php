<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table = 'settings';
    
    protected $fillable = ['theme', 'language'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
