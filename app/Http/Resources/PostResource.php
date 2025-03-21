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

        $data['medias'] = $this->medias;
        $data['user']['id'] = $this->user->id;
        $data['user']['display_name'] = $this->user->profile->display_name;
        $data['user']['profile_photo'] = $this->user->profile->profile_photo;

        return $data;
    }
}
