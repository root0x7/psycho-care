<?php

namespace App\Services\Test;

use App\Models\Test;
use App\Models\TestAttempt;
use App\Models\TestAttemptSectionSummary;
use App\Models\TestInterpretationRule;

class TestScoringService
{
    public function calculateMaxScore(Test $test): int
    {
        return $test->sections()
            ->with('questions.answerOptions')
            ->get()
            ->sum(function ($section) {
                return $section->questions->sum(function ($question) {
                    return $question->answerOptions->max('score') ?? 0;
                });
            });
    }

    public function generateSectionSummaries(TestAttempt $attempt): void
    {
        $attempt->load(['test.sections.questions.answerOptions', 'test.sections.interpretationRules', 'answers']);

        foreach ($attempt->test->sections as $section) {
            $sectionScore = $attempt->answers
                ->whereIn('test_question_id', $section->questions->pluck('id'))
                ->sum('score');

            $maxScore = $section->questions->sum(function ($q) {
                return $q->answerOptions->max('score') ?? 0;
            });

            $interpretation = $this->findInterpretation($section->interpretationRules, $sectionScore);

            TestAttemptSectionSummary::updateOrCreate(
                ['test_attempt_id' => $attempt->id, 'test_section_id' => $section->id],
                [
                    'score'                      => $sectionScore,
                    'max_score'                  => $maxScore,
                    'interpretation_label'       => $interpretation?->label,
                    'interpretation_description' => $interpretation?->description,
                ]
            );
        }
    }

    public function findInterpretation(\Illuminate\Database\Eloquent\Collection $rules, int $score): ?TestInterpretationRule
    {
        return $rules->first(function ($rule) use ($score) {
            return $score >= $rule->score_from && $score <= $rule->score_to;
        });
    }

    public function getTotalInterpretation(TestAttempt $attempt): ?TestInterpretationRule
    {
        $rules = TestInterpretationRule::where('test_id', $attempt->test_id)
            ->whereNull('test_section_id')
            ->get();

        return $this->findInterpretation($rules, $attempt->total_score);
    }
}
