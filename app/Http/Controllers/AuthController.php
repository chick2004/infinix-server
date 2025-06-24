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
        return response()->json([
            "message" => "",
            "data" => new UserResource($request->user()),
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
        ]);

        if ($validator->fails()) {
            return response()->json([
                "message" => "Invalid request data",
                "errors" => $validator->errors(),
                "status" => 422,
            ]);
        }

        if (!Auth::attempt($request->only("email", "password")) || User::where("email", $request->email)->doesntExist()) {
            return response()->json([
                "message" => "Invalid login details",
                "errors" => [
                    "email" => "The provided credentials do not match our records.",
                ],
                "status" => 401,
            ]);
        }

        $request->session()->regenerate();

        return response()->json([
            "message" => "login successful",
            "data" => new UserResource($request->user())
        ], 200);

    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "email" => "required|email",
            "password" => "required",
            "code" => "required",
        ]);

        if ($validator->fails()) {
            return response()->json([
                "message" => "Invalid request data",
                "errors" => $validator->errors(),
                "status" => 422,
            ]);
        }

        $verification_code = VerificationCode::where("email", $request->email)->where("code", $request->code);
        
        if (!$verification_code->exists()) {
            return response()->json([
                "message" => "Invalid code",
                "errors" => [
                    "code" => "The provided verification code is invalid.",
                ],
                "status" => 422,
            ]);
        }

        $verification_code->delete();

        $user = User::create([
            "email" => $request->email,
            "password" => Hash::make($request->password),
            "username" => explode("@", $request->email)[0],
        ]);

        $display_name = $request->display_name ?? preg_replace("/[^a-zA-Z0-9]/", "", explode("@", $request->email)[0])."_".Str::random(5);

        $user->profile()->create([
            "display_name" => $display_name,
        ]);

        Auth::attempt($request->only("email", "password"));

        $request->session()->regenerate();

        return response()->json([
            "message" => "register successful",
            "data" => new UserResource($request->user()),
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
        $request->validate([
            "email" => "required|email",
        ]);

        if (User::where("email", $request->email)->exists()) {
            return response()->json([
                "message" => "Email already exists",
                "errors" => [
                    "email" => "The provided email is already registered.",
                ],
                "status" => 422,
            ]);
        }

        $code = VerificationCode::generate($request->email);

        Mail::to($request->email)->send(new VerificationEmail($code));

        return response()->json([
            "message" => "Verification code sent"
        ], 200);
    }

    public function verify_code(Request $request)
    {
        $request->validate([
            "email" => "required|email",
            "code" => "required",
        ]);

        $verification_code = VerificationCode::where("email", $request->email)->where("code", $request->code);
        
        if (!$verification_code->exists()) {
            return response()->json([
                "message" => "Invalid code",
                "errors" => [
                    "code" => "The provided verification code is invalid.",
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
        $request->validate([
            "email" => "required|email",
            "password" => "required",
        ]);

        $user = User::where("email", $request->email)->firstOrFail();
        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json([
            "message" => "Password reset successfully",
            "status" => 200,
        ]);
    }

    public function change_password(Request $request)
    {
        $request->validate([
            "current_password" => "required",
            "new_password" => "required",
        ]);

        if (!Hash::check($request->current_password, $request->user()->password)) {
            return response()->json([
                "message" => "Invalid current password",
                "errors" => [
                    "current_password" => "The provided current password is incorrect.",
                ],
                "status" => 422,
            ]);
        }

        $user = $request->user();
        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            "message" => "Password changed successfully",
            "status" => 200,
        ]);
    }

    public function show(Request $request, $id)
    {
        $user = User::findOrFail($id);

        return response()->json([
            "message" => "",
            "data" => new UserResource($user),
            "status" => 200,
        ]);
    }

}
