<?php

namespace App\Http\Controllers;

use App\Http\Resources\ConversationResource;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Models\Conversation;
use App\Models\Message;
use App\Http\Resources\MessageResource;


class ConversationController extends Controller
{
    public function index()
    {
    }

    public function store(Request $request)
    {
        if ($request->has("is_group") && $request->boolean("is_group")) {
            $validator = Validator::make($request->all(), [
                "name" => "required|string",
                "image" => "required|image",
                "users" => "array",
                "users.*" => "exists:users,id",
            ], [
                "name.required" => "ERR_REQUIRED",
                "image.required" => "ERR_IMAGE_REQUIRED",
                "users.array" => "ERR_USERS_ARRAY",
                "users.*.exists" => "ERR_USER_NOT_FOUND",
            ]);

            if ($validator->fails()) {
                return response()->json([
                    "message" => "Invalid request data",
                    "errors" => $validator->errors(),
                    "status" => 422,
                ]);
            }

            $conversation_data = $request->only(["is_group", "name"]);
            $conversation_data['is_group'] = $request->boolean('is_group');

            if ($request->has("image")) {
                $media = $request->file("image");
                $media_name = time() . "_" . $media->getClientOriginalName();
                $media_path = $media->storeAs("uploads", $media_name, "public");
                $conversation_data["image"] = url(Storage::url($media_path));
            }

            $conversation = Conversation::create($conversation_data);

            $conversation->users()->attach($request->input("users"));
            $conversation->users()->attach($request->user()->id, ['is_admin' => true]);
            return ConversationResource::make($conversation)->additional([
                "message" => "Conversation created successfully",
                "status" => 201,
            ]);
        } else {
            $validator = Validator::make($request->all(), [
                "with_user" => "required|exists:users,id",
            ], [
                "with_user.required" => "ERR_REQUIRED",
                "with_user.exists" => "ERR_USER_NOT_FOUND",
            ]);

            if ($validator->fails()) {
                return response()->json([
                    "message" => "Invalid request data",
                    "errors" => $validator->errors(),
                    "status"=> 422,
                ]);
            }

            $conversation = Conversation::where("is_group", false)
                ->whereHas("users", function ($query) use ($request) {
                    $query->where("user_id", $request->user()->id);
                })
                ->whereHas("users", function ($query) use ($request) {
                    $query->where("user_id", $request->input("with_user"));
                })
                ->first();

            if ($conversation) {
                return response()->json([
                    "message" => "Conversation already exists",
                    "errors" => [
                        "conversation" => "ERR_CONVERSATION_EXISTS",
                    ],
                    "status" => 409,
                ]);
            }

            $conversation = Conversation::create([
                "is_group" => false,
            ]);

            $conversation->users()->attach([$request->user()->id, $request->input("with_user")]);

            return ConversationResource::make($conversation)->additional([
                "message" => "Conversation created successfully",
                "status" => 201,
            ]);
        }
    }

    public function show($id)
    {
        $conversation = Conversation::findOrFail($id);
        return ConversationResource::make($conversation)->additional([
            "status" => 200,
        ]);
    }

    public function update(Request $request, $id)
    {
        $conversation = Conversation::findOrFail($id);

        $validator = Validator::make($request->all(), [
            "name" => "string",
            "image" => "image",
            "users" => "array",
            "users.*" => "exists:users,id",
        ], [
            "name.string" => "ERR_INVALID_NAME",
            "image.image" => "ERR_INVALID_IMAGE",
            "users.array" => "ERR_USERS_ARRAY",
            "users.*.exists" => "ERR_USER_NOT_FOUND",
        ]);

        if ($validator->fails()) {
            return response()->json([
                "message"=> "Invalid request data",
                "errors" => $validator->errors(),
                "status" => 422,
            ]);
        }

        $conversation->update($request->only(["name", "image"]));

        if ($request->has("users")) {
            $conversation->users()->sync($request->input("users"));
        }

        return ConversationResource::make($conversation)->additional([
            "message" => "Conversation updated successfully",
            "status" => 200,
        ]);
    }

    public function destroy($id)
    {
        $conversation = Conversation::findOrFail($id);
        $conversation->delete();
        return response()->json([
            "message"=> "Conversation deleted successfully",
            "status" => 200,
        ]);
    }

    public function by_user(Request $request, $id)
    {
        $conversations = Conversation::whereHas("users", function ($query) use ($id) {
            $query->where("user_id", $id);
        })->get();
        
        return ConversationResource::collection($conversations)->additional([
            "status" => 200,
        ]);
    }

    public function groups_by_user(Request $request, $id)
    {
        $conversations = Conversation::where("is_group", true)
            ->whereHas("users", function ($query) use ($id) {
                $query->where("user_id", $id);
            })->get();

        return ConversationResource::collection($conversations)->additional([
            "status" => 200,
        ]);
    }

    public function users($id)
    {
        $conversation = Conversation::findOrFail($id);
        return UserResource::collection($conversation->users)->additional([
            "status" => 200,
        ]);
    }

    public function with_user(Request $request, $id)
    {
        $conversation = Conversation::where("is_group", false)
            ->whereHas("users", function ($query) use ($id) {
                $query->where("user_id", $id);
            })
            ->whereHas("users", function ($query) use ($request) {
                $query->where("user_id", $request->user()->id);
            })
            ->first();

        if (!$conversation) {
            $conversation = Conversation::create([
                "is_group" => false,
            ]);
            $conversation->users()->attach([$request->user()->id, $id]);

        }
        return ConversationResource::make($conversation)->additional([
            "status" => 200,
        ]);
    }

    public function pinned_messages($id)
    {
        $messages = Conversation::findOrFail($id)->messages()->where("is_pinned", true)->get();
        return MessageResource::collection($messages)->additional([
            "message" => "Pinned messages retrieved successfully",
            "status" => 200,
        ]);
    }
}
