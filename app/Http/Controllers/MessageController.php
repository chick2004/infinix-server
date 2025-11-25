<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Message;
use App\Http\Resources\MessageResource;
use Illuminate\Support\Facades\Storage;
use App\Services\MessageService;

class MessageController extends Controller
{
    public function index()
    {
    }

    public function store(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            "content" => "nullable|string",
            "reply_to_message_id" => "nullable"
        ], [
            "content.required" => "ERR_REQUIRED",
            "reply_to_message_id.exists" => "ERR_MESSAGE_NOT_FOUND",
        ]);

        if ($validator->fails()) {
            return response()->json([
                "content" => "Invalid request data",
                "errors" => $validator->errors()
            ]);
        }

        $message_data = $request->only(["content", "reply_to_message_id"]);
        $message_data["conversation_id"] = $id;
        $message_data["user_id"] = $request->user()->id;

        $message = Message::create($message_data);

        MessageService::handleMedias($message, $request);

        return MessageResource::make($message)->additional([
            "message" => "Message created successfully",
            "status" => 201,
        ]);
    }

    public function show($id)
    {
        $message = Message::findOrfail($id);
        return MessageResource::make($message)->additional([
            "status" => 200,
        ]);
    }

    public function update(Request $request, $id)
    {

        $validator = Validator::make($request->all(), [
            "content" => "nullable|string",
            "medias" => "nullable|array",
            "medias.*" => "nullable|file",
            "deleted_medias" => "nullable|array",
            "deleted_medias.*" => "nullable|exists:message_medias,id",
        ], [
            "content.string" => "ERR_INVALID_CONTENT",
            "medias.array" => "ERR_INVALID_MEDIAS",
            "medias.*.file" => "ERR_INVALID_MEDIA_FILE",
            "deleted_medias.array" => "ERR_INVALID_DELETED_MEDIAS",
            "deleted_medias.*.exists" => "ERR_INVALID_DELETED_MEDIA_ID",
        ]);

        if ($validator->fails()) {
            return response()->json([
                "message" => "Invalid request data",
                "errors" => $validator->errors(),
                "status" => 422,
            ]);
        }

        $message = Message::findOrfail($id);
        $message->update($request->all());
        $message->is_edited = true;
        $message->save();

        MessageService::handleMedias($message, $request);

        return MessageResource::make($message)->additional([
            "message" => "Message updated successfully",
            "status" => 200,
        ]);
    }

    public function destroy($id)
    {
        $message = Message::findOrfail($id);
        $message->softDelete();
        return response()->json([
            "message" => "Message deleted successfully",
            "status" => 200,
        ]);
    }

    public function restore($id)
    {
        $message = Message::withTrashed()->findOrfail($id);
        $message->restore();
        return MessageResource::make($message)->additional([
            "message" => "Message restored successfully",
            "status" => 200,
        ]);
    }

    public function force_delete($id)
    {
        $message = Message::withTrashed()->findOrfail($id);
        $message->forceDelete();
        return response()->json([
            "message" => "Message permanently deleted successfully",
            "status" => 200,
        ]);
    }
    
    public function by_conversation($id)
    {
        $messages = Message::where("conversation_id", $id)->orderBy("created_at", "desc")->paginate(20);
        return MessageResource::collection($messages)->additional([
            "message" => "Messages retrieved successfully",
            "status" => 200,
        ]);
    }

    public function pin_message(Request $request, $id)
    {
        $message = Message::findOrfail($id);
        $message->is_pinned = !$message->is_pinned; 
        $message->save();

        return response()->json([
            "message" => "Message toggled pin status successfully",
            "status" => 200,
        ]);
    }
}
