<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Profile;

class ProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        foreach ($users as $user) {
            Profile::firstOrCreate(
            ['user_id' => $user->id],
            [
                'display_name' => 'display_name_' . $user->id,
                'profile_photo' => null,
                'cover_photo' => null,
                'bio' => null,
                'date_of_birth' => null,
                'gender' => 'other',
            ]
            );
        }
    }
}
