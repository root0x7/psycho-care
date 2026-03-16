<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weather_snapshots', function (Blueprint $table) {
            $table->id();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->string('condition', 64)->nullable(); // sunny, cloudy, rainy, snowy, etc.
            $table->decimal('temperature_celsius', 5, 2)->nullable();
            $table->unsignedTinyInteger('humidity_percent')->nullable();
            $table->json('raw_data')->nullable();
            $table->string('provider', 64)->nullable();
            $table->timestamp('recorded_at');
            $table->timestamps();

            $table->index(['latitude', 'longitude', 'recorded_at']);
        });

        Schema::create('mood_score_defaults', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('score'); // 1..9
            $table->string('locale', 8)->default('en');
            $table->string('label', 128);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['score', 'locale']);
        });

        Schema::create('patient_mood_score_descriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('score'); // 1..9
            $table->string('label', 128)->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'score']);
        });

        Schema::create('mood_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('score'); // 1..9
            $table->text('note')->nullable();
            $table->string('channel', 32)->default('web');
            // Temporal context
            $table->timestamp('submitted_at');
            $table->string('user_timezone', 64)->default('UTC');
            $table->timestamp('local_submitted_at')->nullable(); // in user TZ, for display
            // Weather context
            $table->foreignId('weather_snapshot_id')->nullable()->constrained()->nullOnDelete();
            // Normalization metadata
            $table->json('context_metadata')->nullable(); // time_of_day_weight, weather_factor, etc.
            $table->decimal('normalized_score', 4, 2)->nullable();
            // Overwrite tracking
            $table->boolean('was_overwritten')->default(false);
            $table->softDeletes();
            $table->timestamps();

            $table->index(['user_id', 'submitted_at']);
            $table->index(['user_id', 'score']);
        });

        Schema::create('mood_entry_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mood_entry_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('previous_score');
            $table->text('previous_note')->nullable();
            $table->unsignedTinyInteger('new_score');
            $table->text('new_note')->nullable();
            $table->string('channel', 32)->nullable();
            $table->timestamp('revised_at');
            $table->timestamps();

            $table->index('mood_entry_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mood_entry_revisions');
        Schema::dropIfExists('mood_entries');
        Schema::dropIfExists('patient_mood_score_descriptions');
        Schema::dropIfExists('mood_score_defaults');
        Schema::dropIfExists('weather_snapshots');
    }
};
