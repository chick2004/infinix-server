<?php

namespace App\Services;

class CommentService
{
    public static function handleMedias($comment, $request)
    {
        if ($request->hasFile("media")) {
            $media = $request->file("media")[0];
            $media_name = time() . "_" . $media->getClientOriginalName();
            $media_path = $media->storeAs("uploads", $media_name, "public");
            $comment->media()->create([
                "post_id" => $comment->id,
                "path" => url(Storage::url($media_path)),
                "type" => $media->getMimeType(),
            ]);
        }
        
        if ($request->input("remove_media") && $comment->media()->exists()) {
            Storage::disk("public")->delete(str_replace("/storage/", "", $comment->media()->first()?->path));
            $comment->media()->delete();
        }
    }
}