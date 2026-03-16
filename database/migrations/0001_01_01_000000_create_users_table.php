<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            // Telegram identity
            $table->string('telegram_id', 64)->nullable()->unique();
            $table->string('telegram_nickname', 128)->nullable();
            $table->string('telegram_username', 128)->nullable();
            // Local login
            $table->string('login', 64)->nullable()->unique();
            $table->string('password')->nullable();
            // Personal info
            $table->string('first_name', 128);
            $table->string('second_name', 128)->nullable();
            $table->string('third_name', 128)->nullable();
            $table->date('birth_date')->nullable();
            $table->string('gender', 16)->nullable();
            $table->string('email', 191)->nullable()->unique();
            $table->string('phone_number', 32)->nullable();
            // Location
            $table->string('region', 128)->nullable();
            $table->string('city', 128)->nullable();
            $table->string('street_address', 255)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            // Preferences
            $table->string('locale', 8)->default('en');
            $table->string('timezone', 64)->default('UTC');
            // Status
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_active_at')->nullable();
            $table->string('registration_status', 32)->default('pending');
            $table->rememberToken();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['registration_status']);
            $table->index(['is_active']);
            $table->index(['telegram_id']);
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
