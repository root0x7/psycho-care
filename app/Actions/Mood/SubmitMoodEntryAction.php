<?php

namespace App\Actions\Mood;

use App\Enums\MoodChannel;
use App\Models\MoodEntry;
use App\Models\MoodEntryRevision;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SubmitMoodEntryAction
{
    private const COOLDOWN_MINUTES = 60;

    public function execute(User $user, int $score, ?string $note, MoodChannel $channel): array
    {
        $now = Carbon::now('UTC');
        $cooldownBoundary = $now->copy()->subMinutes(self::COOLDOWN_MINUTES);

        $recentEntry = MoodEntry::where('user_id', $user->id)
            ->where('submitted_at', '>=', $cooldownBoundary)
            ->latest('submitted_at')
            ->first();

        if ($recentEntry) {
            return [
                'status'        => 'cooldown',
                'recent_entry'  => $recentEntry,
                'can_overwrite' => true,
                'message'       => 'You already submitted a mood within the last hour. Would you like to update it?',
            ];
        }

        $entry = $this->createEntry($user, $score, $note, $channel, $now);

        return [
            'status' => 'created',
            'entry'  => $entry,
        ];
    }

    public function overwrite(User $user, MoodEntry $existingEntry, int $newScore, ?string $newNote, MoodChannel $channel): MoodEntry
    {
        return DB::transaction(function () use ($user, $existingEntry, $newScore, $newNote, $channel) {
            // Save revision history before overwriting
            MoodEntryRevision::create([
                'mood_entry_id'  => $existingEntry->id,
                'previous_score' => $existingEntry->score,
                'previous_note'  => $existingEntry->note,
                'new_score'      => $newScore,
                'new_note'       => $newNote,
                'channel'        => $channel->value,
                'revised_at'     => Carbon::now('UTC'),
            ]);

            $existingEntry->update([
                'score'          => $newScore,
                'note'           => $newNote,
                'channel'        => $channel->value,
                'was_overwritten' => true,
            ]);

            return $existingEntry->refresh();
        });
    }

    private function createEntry(User $user, int $score, ?string $note, MoodChannel $channel, Carbon $now): MoodEntry
    {
        $tz = $user->timezone ?? 'UTC';
        $localNow = $now->copy()->setTimezone($tz);

        $entry = MoodEntry::create([
            'user_id'           => $user->id,
            'score'             => $score,
            'note'              => $note,
            'channel'           => $channel->value,
            'submitted_at'      => $now,
            'user_timezone'     => $tz,
            'local_submitted_at' => $localNow,
        ]);

        event(new \App\Events\MoodEntryCreated($entry));

        return $entry;
    }
}
