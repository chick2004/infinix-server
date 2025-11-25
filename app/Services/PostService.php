<?php

namespace App\Services;

use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use App\Models\Notification;

class PostService
{
    public static function extractTags($post) 
    {
        $content = $post->content ?? '';
        preg_match_all("/#\w+/", $content, $matches);
        $tag_list = $matches[0];
        
        foreach ($tag_list as $tag_item) {
            $tag = Tag::firstOrCreate(["name" => str_replace("#", "", $tag_item)]);
            $post->tags()->attach($tag->id);
        }
    }

    public static function handleMedias($post, $request)
    {
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
    }

    public static function notifyCreatePost($post, $user)
    {
        $friend_ids = $user->friends()->pluck('id')->toArray();
        $follower_ids = $user->followers()->pluck('id')->toArray();
        $receiver_ids = array_merge($friend_ids, $follower_ids);
        
        foreach ($receiver_ids as $receiver_id) {
            Notification::create([
                'receiver_id' => $receiver_id,
                'trigger_id' => $user->id,
                'reference_id' => $post->id,
                'type' => "create_post",
                'is_read' => false,
            ]);
        }
    }

    public static function notifyLikePost($post, $user)
    {
        $receiver_id = $post->user_id;

        if ($receiver_id !== $user->id && Notification::where('receiver_id', $receiver_id)->where('trigger_id', $user->id)->where('type', 'like_post')->where('reference_id', $post->id)->doesntExist()) {
            Notification::create([
                'receiver_id' => $receiver_id,
                'trigger_id' => $user->id,
                'reference_id' => $post->id,
                'type' => 'like_post',
                'is_read' => false,
            ]);
        }
    }
}