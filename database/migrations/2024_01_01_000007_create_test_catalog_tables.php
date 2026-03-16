<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('test_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 191);
            $table->string('slug', 191)->unique();
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('tests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name', 191);
            $table->string('slug', 191)->unique();
            $table->text('description')->nullable();
            $table->string('type', 32)->default('custom'); // yes_no|custom
            $table->unsignedSmallInteger('version')->default(1);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->softDeletes();
            $table->timestamps();

            $table->index(['test_category_id', 'is_active']);
        });

        Schema::create('test_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_id')->constrained()->cascadeOnDelete();
            $table->string('name', 191);
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->unsignedSmallInteger('max_score')->default(0);
            $table->timestamps();

            $table->index(['test_id', 'sort_order']);
        });

        Schema::create('test_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_section_id')->constrained()->cascadeOnDelete();
            $table->text('text');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_required')->default(true);
            $table->timestamps();

            $table->index(['test_section_id', 'sort_order']);
        });

        Schema::create('test_answer_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_question_id')->constrained()->cascadeOnDelete();
            $table->string('text', 511);
            $table->unsignedSmallInteger('score')->default(0);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['test_question_id', 'sort_order']);
        });

        Schema::create('test_interpretation_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('test_section_id')->nullable()->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('score_from');
            $table->unsignedSmallInteger('score_to');
            $table->string('label', 191);
            $table->text('description')->nullable();
            $table->string('severity', 32)->nullable(); // info|warning|danger
            $table->timestamps();

            $table->index(['test_id']);
            $table->index(['test_section_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('test_interpretation_rules');
        Schema::dropIfExists('test_answer_options');
        Schema::dropIfExists('test_questions');
        Schema::dropIfExists('test_sections');
        Schema::dropIfExists('tests');
        Schema::dropIfExists('test_categories');
    }
};
