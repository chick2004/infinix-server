<?php

namespace App\Services;

use App\Models\Post;
use App\Models\Tag;
use App\Models\User;

class PostService
{
    public static function handleExtractTags($post) 
    {
        $content = $post->content ?? '';
        preg_match_all("/#\w+/", $content, $matches);
        $tag_list = $matches[0];
        
        foreach ($tag_list as $tag_item) {
            $tag = Tag::firstOrCreate(["name" => str_replace("#", "", $tag_item)]);
            $post->tags()->attach($tag->id);
        }
    }

    public static function handleMediaUploads($post, $request)
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
}