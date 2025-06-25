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
            Profile::create([
                'user_id' => $user->id,
                'display_name' => fake()->name(),
                'profile_photo' => null,
                'cover_photo' => null,
                'bio' => fake()->sentence(),
                'date_of_birth' => fake()->date(),
                'gender' => ['male', 'female', 'other'][rand(0, 2)],
                'created_at' => now(),
                'updated_at' => now(),
            ]
            );
        }
    }
}
