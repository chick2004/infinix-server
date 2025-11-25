<?php

namespace App\Http\Controllers;

use App\Http\Resources\CommentResource;
use Illuminate\Http\Request;
use App\Models\Comment;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Services\CommentService;

class CommentController extends Controller
{
    public function index()
    {
        //
    }

    public function show($id)
    {
        $comment = Comment::findOrFail($id);
        return CommentResource::make($comment)->additional([
            "status" => 200,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "content" => "required|string",
            "post_id" => "required|exists:posts,id",
            "reply_to_comment_id" => "nullable|exists:comments,id",
        ], [
            "content.required" => "ERR_REQUIRED",
            "post_id.required" => "ERR_POST_NOT_FOUND",
            "reply_to_comment_id.exists" => "ERR_COMMENT_NOT_FOUND",
        ]);

        if ($validator->fails()) {
            return response()->json([
                "message" => "Invalid request data",
                "errors" => $validator->errors(),
                "status" => 422,
            ]);
        }

        $comment_data = $request->only(["content", "post_id", "reply_to_comment_id"]);
        $comment_data["user_id"] = $request->user()->id;

        $comment = Comment::create($comment_data);

        CommentService::handleMedias($comment, $request);
        
        return CommentResource::make($comment)->additional([
            "message" => "Comment created successfully",
            "status" => 201,
        ]);
    }

    public function update(Request $request, $id)
    {

        $validator = Validator::make($request->all(), [
            "content" => "nullable|string",
            "media" => "nullable|array",
            "remove_media" => "boolean",
        ], [
            "content.string" => "ERR_INVALID_CONTENT",
            "media.array" => "ERR_INVALID_MEDIA",
            "remove_media.boolean" => "ERR_INVALID_REMOVE_MEDIA",
        ]);

        if ($validator->fails()) {
            return response()->json([
                "message" => "Invalid request data",
                "errors" => $validator->errors(),
                "status" => 422,
            ]);
        }

        $comment = Comment::findOrFail($id);
        if ($request->has("content")) {
            $comment->content = $request->input("content");
        }

        CommentService::handleMedias($comment, $request);

        if ($request->input("remove_media") && $comment->media()->exists()) {
            Storage::disk("public")->delete(str_replace("/storage/", "", $comment->media()->first()?->path));
            $comment->media()->delete();
        }


        $comment->save();

        return CommentResource::make($comment)->additional([
            "message" => "Comment updated successfully",
            "status" => 200,
        ]);
    }

    public function destroy($id)
    {
        $comment = Comment::findOrFail($id);
        $comment->delete();
        return response()->json([
            "message" => "Comment deleted successfully",
            "status" => 200,
        ]);
    }

    public function by_post($id)
    {
        $comments = Comment::where("post_id", $id)->orderBy("created_at", "desc")->paginate(20);
        return CommentResource::collection($comments)->additional([
            "status" => 200,
        ]);
    }
}
