<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Post;
use App\Models\User;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        foreach ($users as $user) {
            Post::create([
                "user_id"=> $user->id,
                "shared_post_id" => null,
                "content" => fake()->paragraph(),
                "visibility" => ['public', 'private', 'friends'][rand(0, 2)],
                "created_at" => now(),
                "updated_at" => now(),
                "deleted_at" => null,
            ]);
        }
    }
}
