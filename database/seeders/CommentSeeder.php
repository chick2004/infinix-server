<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Comment;
use App\Models\User;
use App\Models\Post;

class CommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $posts = Post::all();

        foreach ($posts as $post) {
            for ($i = 0; $i < rand(150, 200); $i++) {
                Comment::create([
                    'user_id' => User::inRandomOrder()->first()->id,
                    'post_id' => $post->id,
                    'content' => fake()->sentence(),
                    'reply_to_comment_id' => rand(0, 1) ? Comment::inRandomOrder()->where('post_id', $post->id)->first()?->id : null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
