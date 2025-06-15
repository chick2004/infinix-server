<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
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
        $data['media'] = $this->media;

        $data['user']['id'] = $this->user->id;
        $data['user']['profile']['display_name'] = $this->user->profile->display_name;
        $data['user']['profile']['profile_photo'] = $this->user->profile->profile_photo;

        $data['post_id'] = $this->post_id;
        
        $data['created_at'] = $this->created_at;
        $data['updated_at'] = $this->updated_at;
        return $data;
    }
}
