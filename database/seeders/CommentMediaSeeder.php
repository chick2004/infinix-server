<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\CommentMedia;
use App\Models\Comment;

class CommentMediaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $comments = Comment::doesntHave('media')->inRandomOrder()->limit(100)->get();

        foreach ($comments as $comment) {
            CommentMedia::create([
                'comment_id' => $comment->id,
                'path' => 'https://picsum.photos/seed/'.rand(1, 999).'/640/480',
                'type' => 'image/png',
            ]);
        }
    }
}
