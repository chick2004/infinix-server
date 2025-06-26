<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data['id'] = $this->id;
        $data['content'] = $this->content;
        $data['visibility'] = $this->visibility;
        $data['shared_post'] = $this->is_shared ? new PostResource($this->shared_post) : null;
        $data['created_at'] = $this->created_at->format('Y-m-d H:i:s');
        $data['updated_at'] = $this->updated_at->format('Y-m-d H:i:s');
        $data['deleted_at'] = $this->deleted_at?->format('Y-m-d H:i:s');
        $data['like_count'] = $this->like_count;
        $data['is_liked'] = $request->user() ? $this->likes->contains('user_id', $request->user()->id) : false;
        $data['comment_count'] = $this->comment_count ?? 0;
        $data['share_count'] = $this->share_count ?? 0;
        $data['is_bookmarked'] = $request->user() ? $request->user()->bookmarks->contains('post_id', $this->id) : false;
        $data['medias'] = $this->medias ?? 0;
        $data['user']['id'] = $this->user->id;
        $data['user']['profile']['id'] = $this->user->id;
        $data['user']['profile']['display_name'] = $this->user->profile->display_name;
        $data['user']['profile']['profile_photo'] = $this->user->profile->profile_photo;

        return $data;
    }
}
