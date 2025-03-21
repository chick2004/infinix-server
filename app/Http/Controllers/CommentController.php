<?php

namespace App\Http\Controllers;

use App\Http\Resources\CommentResource;
use Illuminate\Http\Request;
use App\Models\Comment;
use Illuminate\Support\Facades\Validator;

class CommentController extends Controller
{
    public function index()
    {
        //
    }

    public function show($id)
    {
        $comment = Comment::findOrFail($id);
        return new CommentResource($comment);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'content' => 'required|string',
            'post_id' => 'required|exists:posts,id',
            'reply_to_comment_id' => 'nullable|exists:comments,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 400);
        }

        $comment_data = $request->only(['content', 'post_id', 'reply_to_comment_id']);
        $comment_data['user_id'] = $request->user()->id;

        $comment = Comment::create($comment_data);

        if ($request->hasFile('medias')) {
            $medias = $request->file('medias');
            foreach ($medias as $media) {
                $media_name = time() . '_' . $media->getClientOriginalName();
                $media_path = $media->storeAs('uploads', $media_name, 'public');
                $comment->medias()->create([
                    'post_id' => $comment->id,
                    'path' => $media_path,
                    'type' => $media->getMimeType(),
                ]);
            }
        }

        return new CommentResource($comment);
    }

    public function update(Request $request, $id)
    {
        $comment = Comment::findOrFail($id);


        if ($request->has('content')) {
            $comment->content = $request->input('content');
        }

        if ($request->hasFile('medias')) {

            $comment->medias()->delete();

            $medias = $request->file('medias');
            foreach ($medias as $media) {
                $media_name = time() . '_' . $media->getClientOriginalName();
                $media_path = $media->storeAs('uploads', $media_name, 'public');
                $comment->medias()->create([
                    'post_id' => $comment->id,
                    'path' => $media_path,
                    'type' => $media->getMimeType(),
                ]);
            }
        }


        $comment->save();

        return new CommentResource($comment);
    }

    public function destroy($id)
    {
        $comment = Comment::findOrFail($id);
        $comment->delete();
        return response()->json([
            'message' => 'Comment deleted successfully'
        ], 200);
    }

    public function by_post($id)
    {
        $comments = Comment::where('post_id', $id)->orderBy('created_at', 'desc')->paginate(20);
        return CommentResource::collection($comments);
    }
}
