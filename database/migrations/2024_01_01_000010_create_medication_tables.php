<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drug master list
        Schema::create('medications', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255)->unique();
            $table->string('generic_name', 255)->nullable();
            $table->string('drug_class', 128)->nullable(); // antidepressant|antipsychotic|mood_stabilizer
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();

            $table->index('drug_class');
        });

        // Switching protocols (antidepressant or antipsychotic)
        Schema::create('medication_switches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('drug_from_id')->constrained('medications')->cascadeOnDelete();
            $table->foreignId('drug_to_id')->constrained('medications')->cascadeOnDelete();
            $table->string('switch_type', 64); // antidepressant_switch|antipsychotic_switch
            $table->text('stopping_doses')->nullable();
            $table->text('starting_doses')->nullable();
            $table->text('notes')->nullable();
            $table->string('source', 128)->nullable();
            $table->timestamp('source_fetched_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['drug_from_id', 'drug_to_id', 'switch_type']);
        });

        // Adding one mood stabilizer while on another
        Schema::create('medication_combinations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('primary_drug_id')->constrained('medications')->cascadeOnDelete();
            $table->foreignId('adding_drug_id')->constrained('medications')->cascadeOnDelete();
            $table->text('main_information')->nullable();
            $table->text('starting_doses')->nullable();
            $table->text('precautions')->nullable();
            $table->string('source', 128)->nullable();
            $table->timestamp('source_fetched_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['primary_drug_id', 'adding_drug_id']);
        });

        // Taper / discontinuation schedule
        Schema::create('medication_taper_guides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medication_id')->constrained()->cascadeOnDelete();
            $table->text('tapering_schedule');
            $table->text('notes')->nullable();
            $table->string('source', 128)->nullable();
            $table->timestamp('source_fetched_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->unique('medication_id');
        });

        // Pregnancy / breastfeeding risk
        Schema::create('medication_pregnancy_risks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medication_id')->constrained()->cascadeOnDelete();
            $table->string('risk_level', 64); // compatible|likely_compatible|limited_compatible|incompatible
            $table->text('description')->nullable();
            $table->string('source', 128)->nullable();
            $table->timestamp('source_fetched_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->unique('medication_id');
            $table->index('risk_level');
        });

        // Drug-drug interactions
        Schema::create('medication_interactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('drug_a_id')->constrained('medications')->cascadeOnDelete();
            $table->foreignId('drug_b_id')->constrained('medications')->cascadeOnDelete();
            $table->boolean('is_safe')->default(true);
            $table->text('description')->nullable();
            $table->string('severity', 32)->nullable(); // safe|caution|avoid
            $table->string('source', 128)->nullable();
            $table->timestamp('source_fetched_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['drug_a_id', 'drug_b_id']);
            $table->index(['drug_a_id', 'drug_b_id', 'is_safe']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medication_interactions');
        Schema::dropIfExists('medication_pregnancy_risks');
        Schema::dropIfExists('medication_taper_guides');
        Schema::dropIfExists('medication_combinations');
        Schema::dropIfExists('medication_switches');
        Schema::dropIfExists('medications');
    }
};
