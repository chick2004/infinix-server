<?php

namespace App\Http\Controllers;

use App\Http\Resources\FriendRequestResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\FriendRequest;
use App\Models\Relationship;

class FriendRequestController extends Controller
{
    public function index()
    {
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "receiver_id" => "required|exists:users,id",
        ]);

        if ($validator->fails()) {
            return response()->json([
                "errors" => $validator->errors(),
                "status" => 400,
            ]);
        }

        $friend_request_data = [
            "sender_id" => $request->user()->id,
            "receiver_id" => $request->input("receiver_id"),
        ];

        $friend_request = FriendRequest::create($friend_request_data);

        return (new FriendRequestResource($friend_request))->additional([
            "message" => "Friend request sent successfully",
            "status" => 201,
        ]);
    }

    public function show($id)
    {
        $friend_request = FriendRequest::findOrFail($id);
        return (new FriendRequestResource($friend_request))->additional([
            "message" => "Friend request sent successfully",
            "status" => 201,
        ]);
    }

    public function update(Request $request, $id)
    {
        $friend_request = FriendRequest::findOrFail($id);

        $validator = Validator::make($request->all(), [
            "status" => "required|in:accepted,rejected",
        ]);

        if ($validator->fails()) {
            return response()->json([
                "errors" => $validator->errors(),
                "status" => 400
            ]);
        }

        $status = $request->input("status");
        if ($status == "accepted") {
            $relationship_data = [
                "user_id" => $friend_request->sender_id,
                "related_user_id" => $friend_request->receiver_id,
                "type" => "friend",
            ];
            Relationship::create($relationship_data);
            $friend_request->delete();
        } else {
            $friend_request->delete();
        }

        return (new FriendRequestResource($friend_request))->additional([
            "message" => "Friend request updated successfully",
            "status" => 200,
        ]);
    }

    public function destroy($id)
    {
        $friend_request = FriendRequest::findOrFail($id);
        $friend_request->delete();
        return response()->json([
            "message" => "Friend request deleted successfully",
            "status" => 200,
        ]);
    }

    public function by_sender(Request $request)
    {
        $friend_requests = FriendRequest::where("sender_id", $request->user()->id)->get();
        return FriendRequestResource::collection($friend_requests)->additional([
            "status" => 200,
        ]);
    }

    public function by_receiver(Request $request)
    {
        $friend_requests = FriendRequest::where("receiver_id", $request->user()->id)->get();
        return FriendRequestResource::collection($friend_requests)->additional([
            "status" => 200,
        ]);
    }

    public function by_user(Request $request, $id)
    {
        $friend_requests = FriendRequest::where("sender_id", $id)
            ->orWhere("receiver_id", $id)
            ->get();
        return FriendRequestResource::collection($friend_requests)->additional([
            "status" => 200,
        ]);
    }

}
