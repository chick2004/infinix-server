<?php

namespace App\Http\Controllers;

use App\Http\Resources\PostResource;
use App\Models\PostBookmark;
use Illuminate\Http\Request;
use App\Models\Post;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Services\PostService;

use App\Models\Tag;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::withTrashed()->orderBy("created_at", "desc")->paginate(20);
        return PostResource::collection($posts)->additional([
            "status" => 200,
        ]);
    }

    public function show($id)
    {
        $post = Post::withTrashed()->findOrFail($id);
        return PostResource::make($post)->additional([
            "status" => 200,
        ]);
    }

    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            "content" => "nullable|string",
            "visibility" => "nullable|in:public,private,friends",
            "is_shared" => "nullable|boolean",
            "shared_post_id" => "nullable|exists:posts,id",
            "medias" => "nullable|array",
            "medias.*" => "nullable|file",
        ], [
            "content.string" => "ERR_INVALID_CONTENT",
            "visibility.in" => "ERR_INVALID_VISIBILITY",
            "is_shared.boolean" => "ERR_INVALID_IS_SHARED",
            "shared_post_id.exists" => "ERR_INVALID_SHARED_POST_ID",
            "medias.array" => "ERR_INVALID_MEDIAS",
            "medias.*.file" => "ERR_INVALID_MEDIA_FILE",
        ]);

        if ($validator->fails()) {
            return response()->json([
                "message" => "Invalid request data",
                "errors" => $validator->errors(),
                "status" => 422,
            ]);
        }


        $post_data = $request->only(["content", "visibility", "is_shared", "shared_post_id"]);
        $post_data["user_id"] = $request->user()->id;

        $post = Post::create($post_data);

        PostService::handleExtractTags($post);
        PostService::handleMediaUploads($post, $request);

        return PostResource::make($post)->additional([
            "message" => "Post created successfully",
            "status" => 201,
        ]);
    }

    public function update(Request $request, $id)
    {

        $validator = Validator::make($request->all(), [
            "content" => "nullable|string",
            "visibility" => "nullable|in:public,private,friends",
            "is_shared" => "nullable|boolean",
            "shared_post_id" => "nullable|exists:posts,id",
            "medias" => "nullable|array",
            "medias.*" => "nullable|file",
        ], [
            "content.string" => "ERR_INVALID_CONTENT",
            "visibility.in" => "ERR_INVALID_VISIBILITY",
            "is_shared.boolean" => "ERR_INVALID_IS_SHARED",
            "shared_post_id.exists" => "ERR_INVALID_SHARED_POST_ID",
            "medias.array" => "ERR_INVALID_MEDIAS",
            "medias.*.file" => "ERR_INVALID_MEDIA_FILE",
        ]);

        if ($validator->fails()) {
            return response()->json([
                "message" => "Invalid request data",
                "errors" => $validator->errors(),
                "status" => 422,
            ]);
        }

        $post = Post::withTrashed()->findOrFail($id);
        $post->update($request->only(["content", "visibility", "is_shared", "shared_post_id"]));

        $post->tags()->detach();

        PostService::handleExtractTags($post);
        PostService::handleMediaUploads($post, $request);

        return PostResource::make($post)->additional([
            "message" => "Post updated successfully",
            "status" => 200,
        ]);
    }

    public function force_destroy($id)
    {
        $post = Post::withTrashed()->findOrFail($id);
        $post->forceDelete();

        return response()->json([
            "message" => "Post deleted successfully",
            "status" => 200,
        ]);
    }

    public function destroy($id)
    {
        $post = Post::withTrashed()->findOrFail($id);
        $post->delete();

        return response()->json([
            "message" => "Post soft deleted successfully",
            "status"=> 200,
        ]);
    }

    public function restore($id)
    {
        $post = Post::withTrashed()->findOrFail($id);
        $post->restore();

        return PostResource::make($post)->additional([
            "message" => "Post restored successfully",
            "status" => 200,
        ]);
    }

    public function by_tag($tag)
    {
        $posts = Post::whereHas("tags", function ($query) use ($tag) {
            $query->where("tag", $tag);
        })->orderBy("created_at", "desc")->paginate(20);

        return PostResource::collection($posts)->additional([
            "status" => 200,
        ]);
    }

    public function by_user($user_id)
    {
        $posts = Post::where("user_id", $user_id)->orderBy("created_at", "desc")->paginate(20);

        return PostResource::collection($posts)->additional([
            "status" => 200,
        ]);
    }

    public function like(Request $request, $id)
    {
        $post = Post::withTrashed()->findOrFail($id);
        $user = $request->user();

        if ($post->likes()->where('user_id', $user->id)->exists()) {
            $post->likes()->where('user_id', $user->id)->delete();
            return response()->json([
                "message" => "Like removed",
                "status" => 200,
            ]);
        } else {
            $post->likes()->create(['user_id' => $user->id]);
            return response()->json([
                "message" => "Post liked",
                "status" => 201,
            ]);
        }
    }
}
