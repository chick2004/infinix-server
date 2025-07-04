<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;

class FriendController extends Controller
{
    /**
     * Lấy danh sách bạn bè của một user
     */
    public function getUserFriends(Request $request, $userId)
    {
        
        $user = User::findOrFail($userId);
        $friends = $user->friends()->get();
        
        return UserResource::collection($friends)->additional([
            'status' => 200,
        ]);
    }
    
    /**
     * Kiểm tra xem 2 user có phải bạn bè không
     */
    public function checkFriendship($userId1, $userId2)
    {
        $isFriend = \DB::table('relationships')
            ->where(function($query) use ($userId1, $userId2) {
                $query->where('user_id', $userId1)
                      ->where('related_user_id', $userId2);
            })
            ->orWhere(function($query) use ($userId1, $userId2) {
                $query->where('user_id', $userId2)
                      ->where('related_user_id', $userId1);
            })
            ->where('type', 'friend')
            ->exists();
            
        return response()->json([
            'success' => true,
            'user1_id' => $userId1,
            'user2_id' => $userId2,
            'are_friends' => $isFriend
        ]);
    }
}
