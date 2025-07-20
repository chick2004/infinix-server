<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use PragmaRX\Google2FA\Google2FA;
use App\Mail\VerificationEmail;
use App\Models\VerificationCode;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;


class AuthController extends Controller
{
    public function user(Request $request) 
    {
        $user = $request->user();
        return UserResource::make($user)->additional([
            "status" => 200,
        ]);
    }

    public function list(Request $request)
    {
        $users = User::where("id", "!=", $request->user()->id)->get();

        return response()->json([
            "message" => "",
            "data" => UserResource::collection($users),
            "status" => 200,
        ]);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "email" => "required|email",
            "password" => "required",
        ], [
            "email.required" => "ERR_REQUIRED",
            "email.email" => "ERR_EMAIL_FORMAT",
            "password.required" => "ERR_REQUIRED",
        ]);

        if ($validator->fails()) {
            return response()->json([
                "message" => "Invalid request data",
                "errors" => $validator->errors(),
                "status" => 422,
            ]);
        }

        if (User::where("email", $request->input("email"))->doesntExist()) {
            return response()->json([
                "message" => "User not found",
                "errors" => [
                    "email" => ["ERR_USER_NOT_FOUND"],
                ],
                "status" => 404,
            ]);
        }

        if (!Auth::attempt($request->only("email", "password"))) {
            return response()->json([
                "message" => "Invalid login details",
                "errors" => [
                    "password" => ["ERR_INVALID_LOGIN"],
                ],
                "status" => 401,
            ]);
        }

        $request->session()->regenerate();
        $user = $request->user();

        return UserResource::make($user)->additional([
            "status" => 200,
        ]);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "email" => "required|email",
            "password" => "required",
            "code" => "required",
        ], [
            "email.required" => "ERR_REQUIRED",
            "email.email" => "ERR_EMAIL_FORMAT",
            "password.required" => "ERR_REQUIRED",
            "code.required" => "ERR_REQUIRED",
        ]);

        if ($validator->fails()) {
            return response()->json([
                "message" => "Invalid request data",
                "errors" => $validator->errors(),
                "status" => 422,
            ]);
        }

        $verification_code = VerificationCode::where("email", $request->input("email"))->where("code", $request->input("code"));
        
        if (!$verification_code->exists()) {
            return response()->json([
                "message" => "Invalid code",
                "errors" => [
                    "code" => ["ERR_INVALID_CODE"],
                ],
                "status" => 422,
            ]);
        }

        $verification_code->delete();

        $user = User::create([
            "email" => $request->input("email"),
            "password" => Hash::make($request->input("password")),
            "username" => explode("@", $request->input("email"))[0],
        ]);

        $display_name = $request->display_name ?? preg_replace("/[^a-zA-Z0-9]/", "", explode("@", $request->input("email"))[0])."_".Str::random(5);

        $user->profile()->create([
            "display_name" => $display_name,
        ]);

        Auth::attempt($request->only("email", "password"));

        $request->session()->regenerate();
        $user = $request->user();

        return UserResource::make($user)->additional([
            "status" => 201,
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            "message" => "Logged out successfully",
            "status" => 200,
        ]);
    }

    public function send_verification_code(Request $request)
    {

        $validator = Validator::make($request->all(), [
            "email" => "required|email",
        ], [
            "email.required" => "ERR_REQUIRED",
            "email.email" => "ERR_EMAIL_FORMAT",
        ]);

        if ($validator->fails()) {
            return response()->json([
                "message" => "Invalid request data",
                "errors" => $validator->errors(),
                "status" => 422,
            ]);
        }

        if (User::where("email", $request->input("email"))->exists()) {
            return response()->json([
                "message" => "Email already exists",
                "errors" => [
                    "email" => ["ERR_EMAIL_ALREADY_EXISTS"],
                ],
                "status" => 422,
            ]);
        }

        $code = VerificationCode::generate($request->input("email"));

        Mail::to($request->input("email"))->send(new VerificationEmail($code));

        return response()->json([
            "message" => "Verification code sent",
            "status" => 200,
        ]);
    }

    public function verify_code(Request $request)
    {

        $validator = Validator::make($request->all(), [
            "email" => "required|email",
            "code" => "required",
        ], [
            "email.required" => "ERR_REQUIRED",
            "email.email" => "ERR_EMAIL_FORMAT",
            "code.required" => "ERR_REQUIRED",
        ]);

        if ($validator->fails()) {
            return response()->json([
                "message" => "Invalid request data",
                "errors" => $validator->errors(),
                "status" => 422,
            ]);
        }

        $verification_code = VerificationCode::where("email", $request->input("email"))->where("code", $request->input("code"));
        
        if (!$verification_code->exists()) {
            return response()->json([
                "message" => "Invalid code",
                "errors" => [
                    "code" => ["ERR_INVALID_CODE"],
                ],
                "status" => 422,
            ]);
        }

        return response()->json([
            "message" => "Code verified",
            "status" => 200,
        ]);
    }

    public function reset_password(Request $request)
    {

        $validator = Validator::make($request->all(), [
            "email" => "required|email",
            "password" => "required",
        ], [
            "email.required" => "ERR_REQUIRED",
            "email.email" => "ERR_EMAIL_FORMAT",
            "password.required" => "ERR_REQUIRED",
        ]);

        if ($validator->fails()) {
            return response()->json([
                "message" => "Invalid request data",
                "errors" => $validator->errors(),
                "status" => 422,
            ]);
        }

        $user = User::where("email", $request->input("email"))->firstOrFail();
        $user->password = Hash::make($request->input("password"));
        $user->save();

        return response()->json([
            "message" => "Password reset successfully",
            "status" => 200,
        ]);
    }

    public function verify_current_password(Request $request)
    {

        $validator = Validator::make($request->all(), [
            "current_password" => "required",
        ], [
            "current_password.required" => "ERR_REQUIRED",
        ]);

        if ($validator->fails()) {
            return response()->json([
                "message" => "Invalid request data",
                "errors" => $validator->errors(),
                "status" => 422,
            ]);
        }

        if (!Hash::check($request->input("current_password"), $request->user()->password)) {
            return response()->json([
                "message" => "Invalid password",
                "errors" => [
                    "current_password" => ["ERR_INVALID_PASSWORD"],
                ],
                "status" => 422,
            ]);
        }

        return response()->json([
            "message" => "Password verified successfully",
            "status" => 200,
        ]);
    }

    public function change_password(Request $request)
    {

        $validator = Validator::make($request->all(), [
            "current_password" => "required",
            "new_password" => "required|min:8",
        ], [
            "current_password.required" => "ERR_REQUIRED",
            "new_password.required" => "ERR_REQUIRED",
            "new_password.min" => "ERR_PASSWORD_MIN_LENGTH",
        ]);

        if ($validator->fails()) {
            return response()->json([
                "message" => "Invalid request data",
                "errors" => $validator->errors(),
                "status" => 422,
            ]);
        }

        if (!Hash::check($request->input("current_password"), $request->user()->password)) {
            return response()->json([
                "message" => "Invalid current password",
                "errors" => [
                    "current_password" => ["ERR_INVALID_PASSWORD"],
                ],
                "status" => 422,
            ]);
        }

        $user = $request->user();
        $user->password = Hash::make($request->input("current_password"));
        $user->save();

        return response()->json([
            "message" => "Password changed successfully",
            "status" => 200,
        ]);
    }

    public function show(Request $request, $id)
    {
        $user = User::findOrFail($id);

        return UserResource::make($user)->additional([
            "status" => 200,
        ]);
    }

}
