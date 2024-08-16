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
            $table->foreignId('notify_user_id')->references('id')->on('users');
            $table->string('notify_user_type')->nullable();
            $table->foreignId('other_user_id')->references('id')->on('users');
            $table->string('other_user_type')->nullable();
            $table->string('title')->nullable();
            $table->string('message')->nullable();
            $table->json('data')->nullable();
            $table->string('notification_type')->nullable();
            $table->boolean('is_read')->default(0);
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
