<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 0; $i < 10; $i++) {
            User::create([
                'username' => fake()->unique()->userName(),
                'email' => fake()->unique()->email(),
                'password' => Hash::make('password'),
                'phone_number' => fake()->phoneNumber(),
                'last_activity' => now(),
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ]);
        }
    }
}
