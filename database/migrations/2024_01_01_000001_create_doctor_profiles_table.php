<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctor_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('specialization', 32)->default('psychologist');
            $table->unsignedTinyInteger('min_patient_age')->default(0);
            $table->text('bio')->nullable();
            $table->json('tags')->nullable();
            $table->string('verification_status', 32)->default('draft');
            $table->text('rejection_reason')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();

            $table->unique('user_id');
            $table->index('verification_status');
        });

        Schema::create('doctor_experience_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_profile_id')->constrained()->cascadeOnDelete();
            $table->string('type', 32); // formal_education | advanced_training | work_history
            $table->string('degree', 32)->nullable(); // for formal education
            $table->string('institution', 255)->nullable();
            $table->string('course_name', 255)->nullable(); // for advanced training
            $table->string('place', 255)->nullable(); // for work history
            $table->string('role', 128)->nullable(); // for work history
            $table->string('years_raw', 16)->nullable(); // e.g. "2016" or "2016-2020"
            $table->unsignedSmallInteger('start_year')->nullable();
            $table->unsignedSmallInteger('end_year')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['doctor_profile_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_experience_entries');
        Schema::dropIfExists('doctor_profiles');
    }
};
