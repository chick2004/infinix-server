<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FriendRequestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data['sender']['id'] = $this->sender->id;
        $data['sender']['display_name'] = $this->sender->profile->display_name;
        $data['sender']['profile_photo'] = $this->sender->profile->profile_photo;

        $data['receiver']['id'] = $this->receiver->id;
        $data['receiver']['display_name'] = $this->receiver->profile->display_name;
        $data['receiver']['profile_photo'] = $this->receiver->profile->profile_photo;

        $data['created_at'] = $this->created_at;
        $data['updated_at'] = $this->updated_at;
        return $data;
    }
}
