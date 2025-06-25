<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\UserSeeder;
use Database\Seeders\ProfileSeeder;
use Database\Seeders\PostSeeder;
use Database\Seeders\PostTagSeeder;
use Database\Seeders\CommentSeeder;
use Database\Seeders\ConversationSeeder;
use Database\Seeders\ConversationUserSeeder;
use Database\Seeders\MessageSeeder;


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
            PostTagSeeder::class,
            CommentSeeder::class,
            ConversationSeeder::class,
            ConversationUserSeeder::class,
            MessageSeeder::class,
        ]);
    }
}
