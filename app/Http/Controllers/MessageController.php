<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Message;
use App\Http\Resources\MessageResource;

class MessageController extends Controller
{
    public function index()
    {
    }

    public function store(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $message_data = $request->only(['message']);
        $message_data['conversation_id'] = $id;
        $message_data['user_id'] = $request->user()->id;

        $message = Message::create($message_data);
        
        if ($request->hasFile('medias')) {
            $medias = $request->file('medias');
            foreach ($medias as $media) {
                $media_name = time() . '_' . $media->getClientOriginalName();
                $media_path = $media->storeAs('uploads', $media_name, 'public');
                $message->medias()->create([
                    'message_id' => $message->id,
                    'path' => $media_path,
                    'type' => $media->getMimeType(),
                ]);
            }
        }

        return new MessageResource($message);
    }

    public function show($id)
    {
        $message = Message::findOrfail($id);
        return new MessageResource($message);
    }

    public function update(Request $request, $id)
    {
        $message = Message::findOrfail($id);
        $message->update($request->all());
        return new MessageResource($message);
    }

    public function destroy($id)
    {
        $message = Message::findOrfail($id);
        $message->softDelete();
        return response()->json(null, 204);
    }

    public function restore($id)
    {
        $message = Message::withTrashed()->findOrfail($id);
        $message->restore();
        return new MessageResource($message);
    }

    public function force_delete($id)
    {
        $message = Message::withTrashed()->findOrfail($id);
        $message->forceDelete();
        return response()->json(null, 204);
    }
    
    public function by_conversation($id)
    {
        $messages = Message::where('conversation_id', $id)->get();
        return MessageResource::collection($messages);
    }
}
