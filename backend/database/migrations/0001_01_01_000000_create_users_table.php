<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Users table
        Schema::create('users', function (Blueprint $table) {
            $table->id('user_id');
            $table->string('username', 50)->unique();
            $table->string('first_name', 100)->nullable();
            $table->string('last_name', 100)->nullable();
            $table->string('email', 255)->unique();
            $table->string('password_hash', 255)->nullable();
            $table->string('google_id', 255)->unique()->nullable();
            $table->string('avatar_url', 255)->nullable();
            $table->text('bio')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamp('updated_at')->nullable();

        });

        // Chats table
        Schema::create('chats', function (Blueprint $table) {
            $table->id('chat_id');
            $table->enum('chat_type', ['one_to_one', 'group']);
            $table->string('chat_name', 255)->nullable();
            $table->string('chat_avatar_url', 255)->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('created_by')
                ->references('user_id')->on('users')
                ->onDelete('set null');
        });

        // Messages table
        Schema::create('messages', function (Blueprint $table) {
            $table->id('message_id');
            $table->unsignedBigInteger('chat_id');
            $table->unsignedBigInteger('sender_id');
            $table->text('content');
            $table->timestamp('sent_at')->useCurrent();
            $table->boolean('is_read')->default(false);

            $table->foreign('chat_id')
                ->references('chat_id')->on('chats')
                ->onDelete('cascade');

            $table->foreign('sender_id')
                ->references('user_id')->on('users')
                ->onDelete('cascade');
        });

        // Chat Participants table
        Schema::create('chat_participants', function (Blueprint $table) {
            $table->id('participant_id');
            $table->unsignedBigInteger('chat_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamp('joined_at')->useCurrent();
            $table->enum('role', ['member', 'admin', 'owner'])->default('member');

            $table->unique(['chat_id', 'user_id']);

            $table->foreign('chat_id')
                ->references('chat_id')->on('chats')
                ->onDelete('cascade');

            $table->foreign('user_id')
                ->references('user_id')->on('users')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_participants');
        Schema::dropIfExists('messages');
        Schema::dropIfExists('chats');
        Schema::dropIfExists('users');
    }
};
