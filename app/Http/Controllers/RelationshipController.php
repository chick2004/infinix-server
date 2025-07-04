<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Relationship;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Resources\UserResource;

class RelationshipController extends Controller
{
    public function friends(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $friends = $user->friends()->with('profile')->get();
        return UserResource::collection($friends)->additional([
            'status' => 200,
        ]);
    }

    public function following(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $following = $user->following()->with('profile')->get();
        return UserResource::collection($following)->additional([
            'status' => 200,
        ]);
    }

    public function follow(Request $request, $id)
    {
        Relationship::create([
            'user_id' => $request->user()->id,
            'related_user_id' => $id,
            'type' => 'follow',
        ]);

        return response()->json([
            'status' => 200,
        ]);
    }

    public function followers(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $followers = $user->followers()->with('profile')->get();
        return UserResource::collection($followers)->additional([
            'status' => 200,
        ]);
    }
}
