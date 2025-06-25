<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Conversation;
use App\Models\ConversationUser;
use App\Models\User;

class ConversationUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $conversations = Conversation::all();
        foreach ($conversations as $conversation) {
            $user_ids = User::pluck('id')->toArray();

            if ($conversation->is_group) {
                $count = rand(3, count($user_ids));
                $selected_user_ids = collect($user_ids)->random($count)->values();
                $admin_index = rand(0, $selected_user_ids->count() - 1);
            } else {
                $selected_user_ids = collect($user_ids)->random(2)->values();
                $admin_index = null;
            }

            foreach ($selected_user_ids as $index => $user_id) {
                ConversationUser::create([
                    'conversation_id' => $conversation->id,
                    'user_id' => $user_id,
                    'is_admin' => ($conversation->is_group && $index === $admin_index) ? true : false,
                ]);
            }
        }
    }
}
