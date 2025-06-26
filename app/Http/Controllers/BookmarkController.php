<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Models\User;
use App\Models\Post;
use App\Http\Resources\PostResource;

class BookmarkController extends Controller
{
    public function index(Request $request)
    {
        $bookmarks = $request->user()->bookmarks()->with("post")->paginate(20);
        return response()->json([
            "data" => $bookmarks,
            "status" => 200,
        ]);
    }

    public function store(Request $request)
    {
        $validatior = Validator::make($request->all(), [
            "post_id" => "required|exists:posts,id",
        ]);

        if ($validatior->fails()) {
            return response()->json([
                "message" => "Invalid request data",
                "errors" => $validatior->errors(),
                "status" => 422,
            ]);
        }

        $user = $request->user();

        if ($user->bookmarks()->where('post_id', $request->post->id)->exists()) {
            $user->bookmarks()->where('post_id', $request->post->id)->delete();
            return response()->json([
                "message" => "Bookmark removed",
                "status" => 200,
            ]);
        } else {
            $user->bookmarks()->create(['post_id' => $request->post->id]);
            return response()->json([
                "message" => "Bookmark added",
                "status" => 201,
            ]);
        }
    }

    public function destroy(Request $request, $id)
    {
        $bookmark = $request->user()->bookmarks()->where("post_id", $id)->firstOrFail();
        $bookmark->delete();

        return response()->json([
            "message" => "Bookmark removed successfully",
            "status" => 200,
        ]);
    }

    public function by_user(Request $request, $userId)
    {
        $bookmarks = User::findOrFail($userId)
            ->bookmarks()
            ->with('post')->get();
        $posts = null;
        foreach ($bookmarks as $bookmark) {
            $posts[] = $bookmark->post;
        }

        return response()->json([
            "data" => PostResource::collection($posts),
            "status" => 200,
        ]);
    }
}
