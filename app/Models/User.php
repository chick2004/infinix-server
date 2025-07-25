<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'username',
        'email',
        'password',
        'phone_number',
        'last_activity',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    public function setting()
    {
        return $this->hasOne(Setting::class);
    }

    public function posts()
    {
        return $this->hasMany(Post::class, 'user_id');
    }

    public function bookmarks()
    {
        return $this->hasMany(PostBookmark::class, 'user_id');
    }

    public function friend_requests_sent()
    {
        return $this->hasMany(FriendRequest::class, 'sender_id');
    }

    public function friend_requests_received()
    {
        return $this->hasMany(FriendRequest::class, 'receiver_id');
    }

    public function friends()
    {
        return User::whereIn('id', function($query) {
            $query->select('related_user_id')
                  ->from('relationships')
                  ->where('user_id', $this->id)
                  ->where('type', 'friend');
        })->orWhereIn('id', function($query) {
            $query->select('user_id')
                  ->from('relationships')
                  ->where('related_user_id', $this->id)
                  ->where('type', 'friend');
        });
    }

    public function followers()
    {
        return $this->belongsToMany(User::class, 'relationships', 'related_user_id', 'user_id')
            ->wherePivot('type', 'follow');
    }

    public function following()
    {
        return $this->belongsToMany(User::class, 'relationships', 'user_id', 'related_user_id')
            ->wherePivot('type', 'follow');
    }

    public function conversations()
    {
        return $this->belongsToMany(Conversation::class, 'conversation_users');
    }

    public function admin_conversations()
    {
        return $this->belongsToMany(Conversation::class, 'conversation_users')
            ->wherePivot('is_admin', true);
    }

    public function nofitications()
    {
        return $this->hasMany(Notification::class, "receiver_id");
    }

    public function getFriendIds()
    {
        $friendIds = collect();
        
        // Lấy những user mà user hiện tại đã kết bạn (user_id -> related_user_id)
        $friendIds1 = \DB::table('relationships')
            ->where('user_id', $this->id)
            ->where('type', 'friend')
            ->pluck('related_user_id');
            
        // Lấy những user đã kết bạn với user hiện tại (related_user_id -> user_id)
        $friendIds2 = \DB::table('relationships')
            ->where('related_user_id', $this->id)
            ->where('type', 'friend')
            ->pluck('user_id');
            
        return $friendIds1->merge($friendIds2)->unique()->values();
    }


    // protected function casts(): array
    // {
    //     return [
    //         'email_verified_at' => 'datetime',
    //         'password' => 'hashed',
    //     ];
    // }
}
