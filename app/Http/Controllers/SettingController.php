<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\SettingResource;
use Illuminate\Http\Request;
use App\Models\Setting;
use App\Models\User;
use App\Models\Profile;
use Illuminate\Support\Facades\Hash;

class SettingController extends Controller
{

    public function show(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $setting = Setting::where('user_id', $user->id)->first();

        if (!$setting) {
            return response()->json([
                "message" => "Setting not found",
                "status" => 404,
            ]);
        }

        return (new SettingResource($setting))->additional([
            "status" => 200,
        ]);
    }

    public function update(Request $request, $id)
    {

        info("request data: " . json_encode($request->all()));

        // Validate the request data
        $request->validate([
            "display_name" => "string|max:255|nullable",
            "bio" => "string|nullable",
            "date_of_birth" => "nullable",
            "cover_photo" => "nullable|image",
            "profile_photo" => "nullable|image",
            "username" => "string|max:255|nullable",
            "email" => "email|max:255|nullable",
            "phone_number" => "string|max:15|nullable",
            "password" => "string|min:8|confirmed|nullable",
            "theme" => "string|in:light,dark,system|nullable",
            "language" => "string|in:en,vi|nullable",
        ]);

        $user = User::findOrFail($id);

        $user->setting->update($request->only(['theme', 'language']));
        $user->profile->update($request->only([
            'display_name', 'bio'
        ]));

        if ($request->has("date_of_birth")) {
            $user->profile->date_of_birth = date('Y-m-d', strtotime($request->date_of_birth));
        }

        $user->update($request->only(['username', 'email', 'phone_number']));

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
            $user->save();
        }

        info("Profile updated successfully", $user->profile->toArray());
        return (new SettingResource($user->setting))->additional([
            "status" => 200,
        ]);
    }
}
