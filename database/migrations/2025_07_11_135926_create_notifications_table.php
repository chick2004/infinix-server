<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('receiver_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('trigger_id')->constrained('users')->onDelete('cascade');
            $table->enum('type', ['create_post', 'like_post', 'comment_post', 'follow', 'mention', 'reply', 'send_friend_request', 'accept_friend_request']);
            $table->unsignedBigInteger('reference_id');
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
