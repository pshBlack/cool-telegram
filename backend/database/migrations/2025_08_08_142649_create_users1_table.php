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
        Schema::create('users1', function (Blueprint $table) {
            $table->id();
            $table->string('username', 32)->unique()->nullable();
            $table->string('email', 64)->unique();
            $table->string('password')->nullable();
            $table->string('google_id', 64)->nullable();
            $table->text('avatar_url')->nullable();
            $table->timestamps(); 
            $table->timestamp('last_seen')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users1');
    }
};