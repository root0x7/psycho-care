<?php

namespace App\Jobs;

use App\Enums\ImportStatus;
use App\Models\ImportJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RunMedicationImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $timeout = 300;

    public function __construct(public readonly ImportJob $importJob) {}

    public function handle(): void
    {
        $this->importJob->update([
            'status'     => ImportStatus::Running->value,
            'started_at' => now(),
        ]);

        try {
            // Adapter pattern: resolve the correct importer by source
            $importer = app("medication.importer.{$this->importJob->source}");
            $importer->run($this->importJob);

            $this->importJob->update([
                'status'       => ImportStatus::Completed->value,
                'completed_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Medication import failed', [
                'import_job_id' => $this->importJob->id,
                'error'         => $e->getMessage(),
            ]);

            $this->importJob->update([
                'status'        => ImportStatus::Failed->value,
                'error_message' => $e->getMessage(),
                'completed_at'  => now(),
            ]);
        }
    }
}
