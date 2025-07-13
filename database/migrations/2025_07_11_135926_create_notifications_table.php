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
            $table->foreignId('receiver_id')->constrained()->onDelete('cascade');
            $table->foreignId('trigger_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['like', 'comment', 'follow', 'mention', 'reply', 'send_friend_request', 'accept_friend_request']);
            $table->boolean('is_read')->default(false);
            $table->string('url')->nullable();
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
