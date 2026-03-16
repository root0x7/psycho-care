<?php

namespace App\Services\Risk;

use App\Enums\RiskLevel;
use App\Models\MoodEntry;
use App\Models\RiskKeyword;
use App\Models\RiskKeywordMatch;
use Illuminate\Support\Collection;

class RiskKeywordDetectionService
{
    /**
     * Scan a mood entry's note for risk keywords.
     * Returns an array of match data with offset, severity, and matched text.
     * Higher severity wins when spans overlap.
     */
    public function analyze(MoodEntry $entry): Collection
    {
        if (empty($entry->note)) {
            return collect();
        }

        $locale = optional($entry->user)->locale?->value ?? 'en';

        $keywords = RiskKeyword::approved()
            ->whereIn('locale', [$locale, 'en'])
            ->get();

        $text = $entry->note;
        $matches = [];

        foreach ($keywords as $keyword) {
            $phrase = $keyword->phrase;
            $pattern = $keyword->is_phrase_match
                ? '/\b' . preg_quote($phrase, '/') . '\b/ui'
                : '/(?<!\p{L})' . preg_quote($phrase, '/') . '(?!\p{L})/ui';

            preg_match_all($pattern, $text, $found, PREG_OFFSET_CAPTURE);

            foreach ($found[0] as [$matchedText, $offset]) {
                $matches[] = [
                    'keyword'      => $keyword,
                    'matched_text' => $matchedText,
                    'offset_start' => $offset,
                    'offset_end'   => $offset + mb_strlen($matchedText),
                    'severity'     => $keyword->severity,
                ];
            }
        }

        // Resolve overlaps: higher severity wins
        $resolved = $this->resolveOverlaps($matches);

        // Persist matches
        foreach ($resolved as $match) {
            RiskKeywordMatch::updateOrCreate(
                [
                    'mood_entry_id'   => $entry->id,
                    'risk_keyword_id' => $match['keyword']->id,
                    'offset_start'    => $match['offset_start'],
                ],
                [
                    'matched_text' => $match['matched_text'],
                    'offset_end'   => $match['offset_end'],
                    'severity'     => $match['severity']->value,
                ]
            );
        }

        return collect($resolved);
    }

    private function resolveOverlaps(array $matches): array
    {
        // Sort by severity descending (critical first), then offset
        $severityOrder = [
            RiskLevel::Critical->value => 3,
            RiskLevel::Medium->value   => 2,
            RiskLevel::Low->value      => 1,
        ];

        // Sort in-place by severity descending (critical first), then offset
        usort($matches, function ($a, $b) use ($severityOrder) {
            $aOrd = $severityOrder[$a['severity']->value] ?? 0;
            $bOrd = $severityOrder[$b['severity']->value] ?? 0;
            return $bOrd <=> $aOrd;
        });

        $resolved = [];
        $occupied = []; // Track [start, end] spans already claimed

        foreach ($matches as $match) {
            $overlaps = false;
            foreach ($occupied as [$start, $end]) {
                if ($match['offset_start'] < $end && $match['offset_end'] > $start) {
                    $overlaps = true;
                    break;
                }
            }
            if (!$overlaps) {
                $resolved[] = $match;
                $occupied[] = [$match['offset_start'], $match['offset_end']];
            }
        }

        return $resolved;
    }
}
