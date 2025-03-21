<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VerificationCode extends Model
{

    protected $table = 'verification_codes';

    protected $fillable = [
        'email',
        'code',
    ];

    public static function generate($email)
    {
        $code = str_pad((string) rand(1, 999999), 6, '0', STR_PAD_LEFT);

        $verification_code = VerificationCode::where('code', $code);

        if ($verification_code->exists()) {

            if ($verification_code->created_at->diffInMinutes() > 5) {
                $verification_code->delete();
            } else {
                return self::generate($email);
            }
        }

        if (VerificationCode::where('email', $email)->exists()) {
            VerificationCode::where('email', $email)->delete();
        }

        VerificationCode::create([
            'email' => $email,
            'code' => $code,
        ]);
        return $code;
    }
}
