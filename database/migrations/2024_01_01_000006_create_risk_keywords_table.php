<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('risk_keywords', function (Blueprint $table) {
            $table->id();
            $table->string('phrase', 255);
            $table->string('locale', 8)->default('en');
            $table->string('severity', 32)->default('low'); // low|medium|critical
            $table->string('status', 32)->default('approved'); // pending|approved|rejected
            $table->boolean('is_phrase_match')->default(false); // exact phrase vs keyword
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['locale', 'status']);
            $table->index('severity');
        });

        Schema::create('risk_keyword_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mood_entry_id')->constrained()->cascadeOnDelete();
            $table->foreignId('risk_keyword_id')->constrained()->cascadeOnDelete();
            $table->string('matched_text', 512);
            $table->unsignedSmallInteger('offset_start');
            $table->unsignedSmallInteger('offset_end');
            $table->string('severity', 32);
            $table->boolean('doctor_notified')->default(false);
            $table->timestamp('notified_at')->nullable();
            $table->timestamps();

            $table->index('mood_entry_id');
            $table->index(['severity', 'doctor_notified']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('risk_keyword_matches');
        Schema::dropIfExists('risk_keywords');
    }
};
