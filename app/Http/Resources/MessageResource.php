<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data['id'] = $this->id;
        $data['conversation_id'] = $this->conversation_id;
        $data['user']['id'] = $this->user->id;
        $data['user']['username'] = $this->user->username;
        $data['user']['profile']['display_name'] = $this->user->profile->display_name;
        $data['user']['profile']['profile_photo'] = $this->user->profile->profile_photo;
        $data['user']['profile']['cover_photo'] = $this->user->profile->cover_photo;
        $data['reply_to'] = new self($this->replyToMessage);
        $data['content'] = $this->content;
        $data['medias'] = $this->medias;
        $data['is_edited'] = $this->is_edited;
        $data['is_deleted'] = $this->is_deleted;
        $data['is_recalled'] = $this->is_recalled;
        $data['created_at'] = $this->created_at;
        $data['updated_at'] = $this->updated_at;

        return $data;
    }
}
