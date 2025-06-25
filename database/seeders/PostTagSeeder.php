<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Post;
use App\Models\Tag;

class PostTagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for($i = 0; $i < 10; $i++) {
            Tag::create([
                "name" => fake()->unique()->word(),
                "created_at" => now(),
                "updated_at" => now(),
            ]);
        }
    }
}
