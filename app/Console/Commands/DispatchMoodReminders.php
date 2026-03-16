<?php

namespace App\Console\Commands;

use App\Jobs\SendMoodReminder;
use App\Models\MoodReminderSlot;
use Carbon\Carbon;
use Illuminate\Console\Command;

class DispatchMoodReminders extends Command
{
    protected $signature   = 'psychocare:dispatch-mood-reminders';
    protected $description = 'Dispatch mood reminder notifications for the current hour';

    public function handle(): void
    {
        $nowUtc = Carbon::now('UTC');

        // Load all active slots with their users' timezones
        $slots = MoodReminderSlot::active()
            ->with('user')
            ->get();

        $dispatched = 0;

        foreach ($slots as $slot) {
            $user = $slot->user;
            if (!$user || !$user->is_active) {
                continue;
            }

            try {
                $userLocalTime = $nowUtc->copy()->setTimezone($user->timezone ?? 'UTC');
                if ($userLocalTime->hour === $slot->hour) {
                    SendMoodReminder::dispatch($slot);
                    $dispatched++;
                }
            } catch (\Throwable $e) {
                // Invalid timezone - skip
            }
        }

        $this->info("Dispatched {$dispatched} mood reminders.");
    }
}
