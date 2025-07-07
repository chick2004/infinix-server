<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\MessageMedia;
use App\Models\Message;

class MessageMediaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $messages = Message::all();

        foreach ($messages as $message) {
            $mediaCount = rand(0, 10);
            for ($i = 0; $i < $mediaCount; $i++) {
                MessageMedia::create([
                    'message_id' => $message->id,
                    'path' => 'https://picsum.photos/seed/'.rand(1, 999).'/640/480',
                    'type' => 'image/png',
                ]);
            }
        }
    }
}
