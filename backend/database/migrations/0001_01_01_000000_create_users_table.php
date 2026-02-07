<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Users table
        Schema::create("users", function (Blueprint $table) {
            // ulid
            $table->ulid("user_id")->primary();

            $table->string("username", 50)->unique();
            $table->string("first_name", 100)->nullable();
            $table->string("last_name", 100)->nullable();
            $table->string("email", 255)->unique();
            $table->string("password");
            $table->string("google_id", 255)->unique()->nullable();
            $table->string("avatar_url", 255)->nullable();
            $table->text("bio")->nullable();
            $table->timestamps();
            $table->timestamp("last_seen_at")->nullable();
        });

        // Chats table
        Schema::create("chats", function (Blueprint $table) {
            $table->ulid("chat_id")->primary();

            $table->enum("chat_type", ["one_to_one", "group"]);
            $table->string("chat_name", 255)->nullable();
            $table->string("chat_avatar_url", 255)->nullable();

            // Foreign Key для ULID
            $table
                ->foreignUlid("created_by")
                ->nullable()
                ->constrained("users", "user_id")
                ->onDelete("set null");

            $table->timestamps();
        });

        // Messages table
        Schema::create("messages", function (Blueprint $table) {
            $table->ulid("message_id")->primary();

            $table
                ->foreignUlid("chat_id")
                ->constrained("chats", "chat_id")
                ->onDelete("cascade");

            $table
                ->foreignUlid("sender_id")
                ->constrained("users", "user_id")
                ->onDelete("cascade");

            $table->text("content")->nullable();
            $table->string("type")->default("text");
            $table->timestamp("sent_at")->useCurrent();
            $table->boolean("is_read")->default(false);

            $table->softDeletes();
        });

        // Chat Participants table
        Schema::create("chat_participants", function (Blueprint $table) {
            $table->ulid("participant_id")->primary();

            $table
                ->foreignUlid("chat_id")
                ->constrained("chats", "chat_id")
                ->onDelete("cascade");

            $table
                ->foreignUlid("user_id")
                ->constrained("users", "user_id")
                ->onDelete("cascade");

            $table->timestamp("joined_at")->useCurrent();
            $table
                ->enum("role", ["member", "admin", "owner"])
                ->default("member");

            $table->unique(["chat_id", "user_id"]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("chat_participants");
        Schema::dropIfExists("messages");
        Schema::dropIfExists("chats");
        Schema::dropIfExists("users");
    }
};
