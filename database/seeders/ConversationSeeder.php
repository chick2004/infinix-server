<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Conversation;

class ConversationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 0; $i < 30; $i++) {
            Conversation::create([
                'is_group' => rand(0, 1) ? true : false,
                'name' => fake()->name(),
                'image' => 'https://picsum.photos/seed/'.rand(1, 999).'/640/480',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
