<?php

namespace App\Http\Controllers;

use App\Http\Resources\PostResource;
use Illuminate\Http\Request;
use App\Models\Post;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::withTrashed()->orderBy('created_at', 'desc')->paginate(20);
        return PostResource::collection($posts);
    }

    public function show($id)
    {
        $post = Post::withTrashed()->findOrFail($id);
        return new PostResource($post);
    }

    public function store(Request $request)
    {

        Log::info(json_encode($request->all()));

        $validator = Validator::make($request->all(), [
            'content' => 'required|string',
            'visibility' => 'required|in:public,private',
            'is_shared' => 'required|boolean',
            'shared_post_id' => 'nullable|exists:posts,id',
            'medias' => 'nullable|array',
            'medias.*' => 'nullable|file',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 400);
        }


        $post_data = $request->only(['content', 'visibility', 'is_shared', 'shared_post_id']);
        $post_data['user_id'] = $request->user()->id;

        $post = Post::create($post_data);

        preg_match_all('/#\w+/', $request->input('content'), $matches);
        $tags = $matches[0];
        foreach ($tags as $tag) {
            $tag = str_replace('#', '', $tag);
            $post->tags()->create([
                'post_id' => $post->id,
                'tag' => $tag,
            ]);
        }

        if ($request->hasFile('medias')) {
            $medias = $request->file('medias');
            foreach ($medias as $media) {
                $media_name = time() . '_' . $media->getClientOriginalName();
                $media_path = $media->storeAs('uploads', $media_name, 'public');
                $post->medias()->create([
                    'post_id' => $post->id,
                    'path' => $media_path,
                    'type' => $media->getMimeType(),
                ]);
            }
        }

        return response()->json([
            'message' => 'Post created successfully'
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $post = Post::withTrashed()->findOrFail($id);
        $post->update($request->only(['content', 'visibility', 'is_shared', 'shared_post_id']));

        $post->tags()->delete();

        preg_match_all('/#\w+/', $request->input('content'), $matches);
        $tags = $matches[0];
        foreach ($tags as $tag) {
            $tag = str_replace('#', '', $tag);
            $post->tags()->create([
                'post_id' => $post->id,
                'tag' => $tag,
            ]);
        }

        if ($request->has('medias')) {

            $post->medias()->delete();

            $medias = $request->file('medias');
            foreach ($medias as $media) {
                $media_name = time() . '_' . $media->getClientOriginalName();
                $media_path = $media->storeAs('uploads', $media_name, 'public');
                $post->medias()->create([
                    'post_id' => $post->id,
                    'path' => $media_path,
                    'type' => $media->getMimeType(),
                ]);

            }
        }

        return new PostResource($post);
    }

    public function force_destroy($id)
    {
        $post = Post::withTrashed()->findOrFail($id);
        $post->forceDelete();

        return response()->json([
            'message' => 'Post deleted successfully'
        ], 200);
    }

    public function destroy($id)
    {
        $post = Post::withTrashed()->findOrFail($id);
        $post->delete();

        return response()->json([
            'message' => 'Post soft deleted successfully'
        ], 204);
    }

    public function restore($id)
    {
        $post = Post::withTrashed()->findOrFail($id);
        $post->restore();

        return new PostResource($post);
    }

    public function by_tag($tag)
    {
        $posts = Post::whereHas('tags', function ($query) use ($tag) {
            $query->where('tag', $tag);
        })->orderBy('created_at', 'desc')->paginate(20);

        return PostResource::collection($posts);
    }

    public function by_user($user_id)
    {
        $posts = Post::where('user_id', $user_id)->orderBy('created_at', 'desc')->paginate(20);

        return PostResource::collection($posts);
    }
}
