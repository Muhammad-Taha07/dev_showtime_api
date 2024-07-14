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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('profile_image')->nullable();
            $table->text('address')->nullable();
            $table->string('longitude')->nullable();
            $table->string('latitude')->nullable();
            // $table->unsignedInteger('verify_code', FALSE)->nullable();
            $table->string('fcm_token', 255)->nullable();
            $table->dateTime('last_login')->nullable();
            $table->string('device_id', 255)->nullable();
            $table->string('device_type', 255)->nullable();
            $table->boolean('is_verified')->default(0);
            $table->tinyInteger('is_admin')->default(0);
            $table->tinyInteger('status')->default(0);
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
