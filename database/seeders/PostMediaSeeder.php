<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PostMedia;
use App\Models\Post;

class PostMediaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $posts = Post::all();

        foreach ($posts as $post) {
            $mediaCount = rand(0, 10);
            for ($i = 0; $i < $mediaCount; $i++) {
                PostMedia::create([
                    'post_id' => $post->id,
                    'path' => 'https://picsum.photos/seed/'.rand(1, 999).'/640/480',
                    'type' => 'image/png',
                ]);
            }
        }
    }
}
