<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = parent::toArray($request);
        $data['profile'] = $this->profile;
        $data['is_friend'] = $request->user() && $request->user()->id !== $this->id && $data['is_friend'] = $request->user()->friends()->where('users.id', $this->id)->exists();
        $data['is_following'] = $request->user() && $request->user()->id !== $this->id && $request->user()->following()->where('related_user_id', $this->id)->exists();
        $data['is_follower'] = $request->user() && $request->user()->id !== $this->id && $request->user()->followers()->where('user_id', $this->id)->exists();
        $data['is_sent_friend_request'] = $request->user() && $request->user()->id !== $this->id && $request->user()->friend_requests_sent()->where('receiver_id', $this->id)->exists();
        
        return $data;
    }
}
