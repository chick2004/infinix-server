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
        for ($i = 0; $i < 10; $i++) {
            Conversation::create([
                'is_group' => rand(0, 1) ? true : false,
                'name' => fake()->name(),
                'image' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
