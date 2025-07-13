<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Message;
use App\Http\Resources\MessageResource;
use Illuminate\Support\Facades\Storage;

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
        ]);

        if ($validator->fails()) {
            return response()->json([
                "content" => "Invalid request data",
                "errors" => $validator->errors()
            ]);
        }

        info("request data", $request->all());

        $message_data = $request->only(["content", "reply_to_message_id"]);
        $message_data["conversation_id"] = $id;
        $message_data["user_id"] = $request->user()->id;

        $message = Message::create($message_data);
        
        if ($request->hasFile("medias")) {
            $medias = $request->file("medias");
            foreach ($medias as $media) {
                $media_name = time() . "_" . $media->getClientOriginalName();
                $media_path = $media->storeAs("uploads", $media_name, "public");
                $message->medias()->create([
                    "message_id" => $message->id,
                    "path" => url(Storage::url($media_path)),
                    "type" => $media->getMimeType(),
                ]);
            }
        }

        return (new MessageResource($message))->additional([
            "message" => "Message sent successfully",
            "status" => 201,
        ]);
    }

    public function show($id)
    {
        $message = Message::findOrfail($id);
        return (new MessageResource($message))->additional([
            "message" => "Message retrieved successfully",
            "status" => 200,
        ]);
    }

    public function update(Request $request, $id)
    {
        $message = Message::findOrfail($id);
        $message->update($request->all());
        $message->is_edited = true;
        $message->save();

        if ($request->has("medias")) {

            $medias = $request->file("medias");
            foreach ($medias as $media) {
                $media_name = time() . "_" . $media->getClientOriginalName();
                $media_path = $media->storeAs("uploads", $media_name, "public");
                $message->medias()->create([
                    "message_id" => $message->id,
                    "path" => url(Storage::url($media_path)),
                    "type" => $media->getMimeType(),
                ]);
            }
        }

        if ($request->has("deleted_medias")) {
            $deleted_medias = $request->input("deleted_medias");
            foreach ($deleted_medias as $media_id) {
                $media = $message->medias()->find($media_id);
                if ($media) {
                    if ($media->path) {
                        $mediaPath = str_replace("/storage/", "", $media->path);
                        Storage::disk("public")->delete($mediaPath);
                    }
                    $media->delete();
                }
            }
        }

        return (new MessageResource($message))->additional([
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
        return (new MessageResource($message))->additional([
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
