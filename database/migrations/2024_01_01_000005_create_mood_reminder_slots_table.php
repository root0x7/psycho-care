<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mood_reminder_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('hour'); // 0..23
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['user_id', 'hour']);
            $table->index(['is_active', 'hour']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mood_reminder_slots');
    }
};
