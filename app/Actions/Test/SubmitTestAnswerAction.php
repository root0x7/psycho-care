<?php

namespace App\Actions\Test;

use App\Enums\TestAttemptStatus;
use App\Models\Test;
use App\Models\TestAttempt;
use App\Models\TestAttemptAnswer;
use App\Models\TestAttemptSectionSummary;
use App\Models\User;
use App\Services\Test\TestScoringService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SubmitTestAnswerAction
{
    public function __construct(private TestScoringService $scoringService) {}

    public function startAttempt(User $user, Test $test): TestAttempt
    {
        return DB::transaction(function () use ($user, $test) {
            // Build snapshot of current test structure for historical accuracy
            $test->load(['sections.questions.answerOptions', 'sections.interpretationRules', 'interpretationRules']);
            $snapshot = $test->toArray();

            return TestAttempt::create([
                'user_id'      => $user->id,
                'test_id'      => $test->id,
                'test_version' => $test->version,
                'test_snapshot' => $snapshot,
                'status'       => TestAttemptStatus::InProgress->value,
                'started_at'   => Carbon::now('UTC'),
                'max_score'    => $this->scoringService->calculateMaxScore($test),
            ]);
        });
    }

    public function recordAnswer(
        TestAttempt $attempt,
        int $questionId,
        int $answerOptionId,
        int $score,
        ?int $timeSpentSeconds = null
    ): TestAttemptAnswer {
        return TestAttemptAnswer::updateOrCreate(
            ['test_attempt_id' => $attempt->id, 'test_question_id' => $questionId],
            [
                'test_answer_option_id' => $answerOptionId,
                'score'                 => $score,
                'time_spent_seconds'    => $timeSpentSeconds,
                'answered_at'           => Carbon::now('UTC'),
            ]
        );
    }

    public function completeAttempt(TestAttempt $attempt): TestAttempt
    {
        return DB::transaction(function () use ($attempt) {
            $now = Carbon::now('UTC');
            $totalScore = $attempt->answers()->sum('score');
            $duration = $now->diffInSeconds($attempt->started_at);

            $attempt->update([
                'status'           => TestAttemptStatus::Completed->value,
                'total_score'      => $totalScore,
                'completed_at'     => $now,
                'duration_seconds' => $duration,
            ]);

            // Generate section summaries
            $this->scoringService->generateSectionSummaries($attempt);

            return $attempt->refresh();
        });
    }

    public function abandonAttempt(TestAttempt $attempt): TestAttempt
    {
        $attempt->update(['status' => TestAttemptStatus::Abandoned->value]);
        return $attempt->refresh();
    }
}
