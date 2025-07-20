<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\SettingResource;
use Illuminate\Http\Request;
use App\Models\Setting;
use App\Models\User;
use App\Models\Profile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

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

        return SettingResource::make($setting)->additional([
            "status" => 200,
        ]);
    }

    public function update(Request $request, $id)
    {

        info("request data: " . json_encode($request->all()));

        // Validate the request data
        $validator = Validator::make($request->all(), [
            "display_name" => "string|max:255|nullable",
            "bio" => "string|nullable",
            "date_of_birth" => "nullable",
            "cover_photo" => "nullable|image",
            "profile_photo" => "nullable|image",
            "username" => "string|max:255|nullable|unique:users|min:8",
            "email" => "email|max:255|nullable",
            "phone_number" => "string|max:15|nullable",
            "password" => "string|min:8|confirmed|nullable",
            "theme" => "string|in:light,dark,system|nullable",
            "language" => "string|in:en,vi|nullable",
        ], [
            "display_name.string" => "ERR_INVALID_DISPLAY_NAME",
            "bio.string" => "ERR_INVALID_BIO",
            "date_of_birth.date" => "ERR_INVALID_DATE_OF_BIRTH",
            "cover_photo.image" => "ERR_INVALID_COVER_PHOTO",
            "profile_photo.image" => "ERR_INVALID_PROFILE_PHOTO",
            "username.string" => "ERR_INVALID_USERNAME",
            "username.unique" => "ERR_USERNAME_TAKEN",
            "email.email" => "ERR_INVALID_EMAIL",
            "phone_number.string" => "ERR_INVALID_PHONE_NUMBER",
            "password.string" => "ERR_INVALID_PASSWORD",
            "theme.in" => "ERR_INVALID_THEME",
            "language.in" => "ERR_INVALID_LANGUAGE",
        ]);

        if ($validator->fails()) {
            return response()->json([
                "message" => "Validation failed",
                "errors" => $validator->errors(),
                "status" => 422,
            ]);
        }

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
            $user->password = Hash::make($request->input());
            $user->save();
        }

        info("Profile updated successfully", $user->profile->toArray());
        return SettingResource::make($user->setting)->additional([
            "status" => 200,
        ]);
    }
}
