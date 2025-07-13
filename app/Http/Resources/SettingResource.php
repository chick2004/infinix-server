<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SettingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        //$user = $this->user;
        return [
            'id' => $this->id,
            // 'user_id' => $this->user_id,
            // 'display_name' => $user->profile->display_name,
            // 'bio' => $user->profile->bio,
            // 'date_of_birth' => $user->profile->date_of_birth,
            // 'cover_photo' => $user->profile->cover_photo,
            // 'profile_photo' => $user->profile->profile_photo,
            // 'username' => $user->username,
            // 'email' => $user->email,
            // 'phone_number' => $user->phone_number,
            'theme' => $this->theme,
            'language' => $this->language,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
