<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Message;
use App\Models\User;
use App\Models\Conversation;

class MessageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $conversations = Conversation::all();

        foreach ($conversations as $conversation) {
            for ($i = 0; $i < rand(25, 75); $i++) {
                Message::create([
                    'user_id' => $conversation->users->random()->id,
                    'conversation_id' => $conversation->id,
                    'content' => fake()->sentence(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
