<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\UserSeeder;
use Database\Seeders\ProfileSeeder;
use Database\Seeders\PostSeeder;
use Database\Seeders\PostMediaSeeder;
use Database\Seeders\PostTagSeeder;
use Database\Seeders\CommentSeeder;
use Database\Seeders\CommentMediaSeeder;
use Database\Seeders\ConversationSeeder;
use Database\Seeders\ConversationUserSeeder;
use Database\Seeders\MessageSeeder;
use Database\Seeders\MessageMediaSeeder;


class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            ProfileSeeder::class,
            PostSeeder::class,
            PostMediaSeeder::class,
            PostTagSeeder::class,
            CommentSeeder::class,
            CommentMediaSeeder::class,
            ConversationSeeder::class,
            ConversationUserSeeder::class,
            MessageSeeder::class,
            MessageMediaSeeder::class,
        ]);
    }
}
