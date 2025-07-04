<?php

namespace App\Http\Controllers;

use App\Http\Resources\PostResource;
use App\Models\PostBookmark;
use Illuminate\Http\Request;
use App\Models\Post;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

use App\Models\Tag;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::withTrashed()->orderBy("created_at", "desc")->paginate(20);
        return PostResource::collection($posts)->additional([
            "status" => 200,
            "message" => "Posts retrieved successfully",
        ]);
    }

    public function show($id)
    {
        $post = Post::withTrashed()->findOrFail($id);
        return response()->json([
            "data" => new PostResource($post),
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

        preg_match_all("/#\w+/", $request->input("content"), $matches);
        $tag_list = $matches[0];
        foreach ($tag_list as $tag_item) {
            $tag = Tag::firstOrCreate(["name" => str_replace("#", "", $tag_item)]);
            $post->tags()->attach($tag->id);
        }

        if ($request->hasFile("medias")) {
            $medias = $request->file("medias");
            foreach ($medias as $media) {
                $media_name = time() . "_" . $media->getClientOriginalName();
                $media_path = $media->storeAs("uploads", $media_name, "public");
                $post->medias()->create([
                    "post_id" => $post->id,
                    "path" => url(Storage::url($media_path)),
                    "type" => $media->getMimeType(),
                ]);
            }
        }

        return response()->json([
            "message" => "Post created successfully",
            "data" => new PostResource($post),
            "status" => 201,
        ]);
    }

    public function update(Request $request, $id)
    {

        $post = Post::withTrashed()->findOrFail($id);
        $post->update($request->only(["content", "visibility", "is_shared", "shared_post_id"]));

        $post->tags()->detach();

        preg_match_all("/#\w+/", $request->input("content"), $matches);
        $tag_list = $matches[0];
        foreach ($tag_list as $tag_item) {
            $tag = Tag::firstOrCreate(["name" => str_replace("#", "", $tag_item)]);
            $post->tags()->attach($tag->id);
        }

        if ($request->has("medias")) {

            $medias = $request->file("medias");
            foreach ($medias as $media) {
                $media_name = time() . "_" . $media->getClientOriginalName();
                $media_path = $media->storeAs("uploads", $media_name, "public");
                $post->medias()->create([
                    "post_id" => $post->id,
                    "path" => url(Storage::url($media_path)),
                    "type" => $media->getMimeType(),
                ]);
            }
        }

        if ($request->has("deleted_medias")) {
            $deleted_medias = $request->input("deleted_medias");
            foreach ($deleted_medias as $media_id) {
                $media = $post->medias()->find($media_id);
                if ($media) {
                    if ($media->path) {
                        $mediaPath = str_replace("/storage/", "", $media->path);
                        Storage::disk("public")->delete($mediaPath);
                    }
                    $media->delete();
                }
            }
        }

        return response()->json([
            "message" => "Post updated successfully",
            "data" => new PostResource($post),
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

        return response()->json([
            "message" => "Post restored successfully",
            "data" => new PostResource($post),
            "status" => 200,
        ]);
    }

    public function by_tag($tag)
    {
        $posts = Post::whereHas("tags", function ($query) use ($tag) {
            $query->where("tag", $tag);
        })->orderBy("created_at", "desc")->paginate(20);

        return response()->json([
            "data" => PostResource::collection($posts),
            "status" => 200,
        ]);
    }

    public function by_user($user_id)
    {
        $posts = Post::where("user_id", $user_id)->orderBy("created_at", "desc")->paginate(20);

        return response()->json([
            "data" => PostResource::collection($posts),
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
