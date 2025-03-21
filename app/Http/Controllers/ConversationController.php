<?php

namespace App\Http\Controllers;

use App\Http\Resources\ConversationResource;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Conversation;


class ConversationController extends Controller
{
    public function index()
    {
    }

    public function store(Request $request)
    {
        if ($request->has('is_group') && $request->input('is_group') == true) {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
                'image' => 'required|image',
                'users' => 'array',
                'users.*' => 'exists:users,id',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 400);
            }

            $conversation_data = $request->only(['is_group', 'name', 'image']);
            $conversation = Conversation::create($conversation_data);

            $conversation->users()->attach($request->input('users'));
            return new ConversationResource($conversation);
        } else {
            $validator = Validator::make($request->all(), [
                'with_user' => 'required|exists:users,id',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 400);
            }

            $conversation = Conversation::where('is_group', false)
                ->whereHas('users', function ($query) use ($request) {
                    $query->where('user_id', $request->user()->id);
                })
                ->whereHas('users', function ($query) use ($request) {
                    $query->where('user_id', $request->input('with_user'));
                })
                ->first();

            if ($conversation) {
                return response()->json([
                    'errors' => 'Conversation already exists'
                ], 400);
            }

            $conversation = Conversation::create([
                'is_group' => false,
            ]);

            $conversation->users()->attach([$request->user()->id, $request->input('with_user')]);

            return new ConversationResource($conversation);
        }
    }

    public function show($id)
    {
        $conversation = Conversation::findOrFail($id);
        return new ConversationResource($conversation);
    }

    public function update(Request $request, $id)
    {
        $conversation = Conversation::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'string',
            'image' => 'image',
            'users' => 'array',
            'users.*' => 'exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $conversation->update($request->only(['name', 'image']));

        if ($request->has('users')) {
            $conversation->users()->sync($request->input('users'));
        }

        return new ConversationResource($conversation);
    }

    public function destroy($id)
    {
        $conversation = Conversation::findOrFail($id);
        $conversation->delete();
        return response()->json(null, 204);
    }

    public function by_user(Request $request, $id)
    {
        $conversations = Conversation::whereHas('users', function ($query) use ($id) {
            $query->where('user_id', $id);
        })->get();

        return ConversationResource::collection($conversations);
    }

    public function users($id)
    {
        $conversation = Conversation::findOrFail($id);
        return UserResource::collection($conversation->users);
    }
}
