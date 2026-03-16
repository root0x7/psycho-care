<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('telegram_bot_states', function (Blueprint $table) {
            $table->id();
            $table->string('telegram_id', 64)->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('current_state', 64)->nullable(); // waiting_score|waiting_note|etc.
            $table->json('context')->nullable(); // state machine context
            $table->timestamp('last_interaction_at')->nullable();
            $table->timestamps();

            $table->index('telegram_id');
            $table->index('current_state');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('telegram_bot_states');
    }
};
