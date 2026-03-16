<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('test_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('test_id')->constrained()->cascadeOnDelete();
            // Snapshot of test version at time of attempt
            $table->unsignedSmallInteger('test_version')->default(1);
            $table->json('test_snapshot')->nullable(); // Full test structure snapshot
            $table->string('status', 32)->default('in_progress');
            $table->unsignedSmallInteger('total_score')->default(0);
            $table->unsignedSmallInteger('max_score')->default(0);
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['test_id', 'status']);
        });

        Schema::create('test_attempt_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_attempt_id')->constrained()->cascadeOnDelete();
            $table->foreignId('test_question_id')->constrained()->cascadeOnDelete();
            $table->foreignId('test_answer_option_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedSmallInteger('score')->default(0);
            $table->unsignedInteger('time_spent_seconds')->nullable();
            $table->timestamp('answered_at')->nullable();
            $table->timestamps();

            $table->unique(['test_attempt_id', 'test_question_id']);
            $table->index('test_attempt_id');
        });

        Schema::create('test_attempt_section_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_attempt_id')->constrained()->cascadeOnDelete();
            $table->foreignId('test_section_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('score')->default(0);
            $table->unsignedSmallInteger('max_score')->default(0);
            $table->string('interpretation_label', 191)->nullable();
            $table->text('interpretation_description')->nullable();
            $table->timestamps();

            $table->unique(['test_attempt_id', 'test_section_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('test_attempt_section_summaries');
        Schema::dropIfExists('test_attempt_answers');
        Schema::dropIfExists('test_attempts');
    }
};
