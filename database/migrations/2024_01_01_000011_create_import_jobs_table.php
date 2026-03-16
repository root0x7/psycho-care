<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('import_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('source', 128); // psychiatrienet|elactancia|drugs_com|manual
            $table->string('type', 128); // medication_switches|interactions|pregnancy_risk|etc.
            $table->string('status', 32)->default('pending');
            $table->unsignedInteger('records_processed')->default(0);
            $table->unsignedInteger('records_imported')->default(0);
            $table->unsignedInteger('records_failed')->default(0);
            $table->text('error_message')->nullable();
            $table->foreignId('triggered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['source', 'status']);
        });

        Schema::create('import_source_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('import_job_id')->constrained()->cascadeOnDelete();
            $table->string('source_url', 512)->nullable();
            $table->longText('raw_content')->nullable();
            $table->string('content_hash', 64)->nullable();
            $table->timestamp('fetched_at');
            $table->timestamps();

            $table->index('import_job_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_source_snapshots');
        Schema::dropIfExists('import_jobs');
    }
};
