<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConversationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $users = $this->users;
        $the_other = $users->where('id', '!=', $request->user()->id)->first();

        $data['id'] = $this->id;
        $data['is_group'] = $this->is_group;
        $data['name'] = $this->name ?? ($the_other ? $the_other->profile->display_name : null);
        $data['image'] = $this->image ?? ($the_other ? $the_other->profile->profile_photo : null);

        return $data;
    }
}
