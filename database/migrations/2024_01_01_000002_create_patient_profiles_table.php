<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('biography')->nullable();
            $table->string('marital_status', 32)->nullable();
            $table->boolean('family_psychiatric_history')->default(false);
            $table->boolean('childhood_trauma')->default(false);
            $table->boolean('somatic_disease_history')->default(false);
            $table->text('somatic_disease_details')->nullable();
            $table->boolean('suicide_history')->default(false);
            $table->string('suicide_who', 255)->nullable();
            $table->boolean('impulse_control_disorders')->default(false);
            $table->boolean('trauma_orphanage')->default(false);
            $table->boolean('trauma_prison')->default(false);
            $table->boolean('trauma_sexual_violence')->default(false);
            $table->boolean('drug_use_history')->default(false);
            $table->boolean('gambling_addiction')->default(false);
            $table->boolean('subjective_addiction')->default(false);
            $table->text('subjective_addiction_details')->nullable();
            $table->boolean('psychosomatic_disorder')->default(false);
            $table->boolean('previous_psychological_treatment')->default(false);
            $table->text('psychological_treatment_details')->nullable();
            $table->boolean('intake_completed')->default(false);
            $table->timestamp('intake_completed_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->unique('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_profiles');
    }
};
