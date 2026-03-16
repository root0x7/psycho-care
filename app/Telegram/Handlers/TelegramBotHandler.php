<?php

namespace App\Telegram\Handlers;

use App\Actions\Mood\SubmitMoodEntryAction;
use App\Enums\MoodChannel;
use App\Models\TelegramBotState;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Handles incoming Telegram bot updates.
 * Uses a simple state machine stored in telegram_bot_states table.
 *
 * States:
 * - idle: Default, shows menu
 * - waiting_mood_score: Waiting for score 1-9
 * - waiting_mood_note: Waiting for optional note after score
 * - waiting_overwrite_confirm: Asking if user wants to overwrite recent entry
 */
class TelegramBotHandler
{
    public function __construct(
        private SubmitMoodEntryAction $submitMoodAction
    ) {}

    public function handle(array $update): void
    {
        $message = $update['message'] ?? $update['callback_query']['message'] ?? null;
        $callbackQuery = $update['callback_query'] ?? null;

        if (!$message && !$callbackQuery) {
            return;
        }

        $telegramId = (string) ($message['from']['id'] ?? $callbackQuery['from']['id'] ?? null);

        if (!$telegramId) {
            return;
        }

        $user = User::where('telegram_id', $telegramId)->first();

        if (!$user) {
            $this->sendMessage($telegramId, "Please register first via our website and link your Telegram account.");
            return;
        }

        $state = TelegramBotState::firstOrCreate(
            ['telegram_id' => $telegramId],
            ['user_id' => $user->id, 'current_state' => 'idle', 'context' => []]
        );

        $state->update(['user_id' => $user->id, 'last_interaction_at' => now()]);

        if ($callbackQuery) {
            $this->handleCallback($user, $state, $callbackQuery);
        } elseif (isset($message['text'])) {
            $this->handleText($user, $state, $message['text']);
        }
    }

    private function handleText(User $user, TelegramBotState $state, string $text): void
    {
        $text = trim($text);

        match ($state->current_state) {
            'idle' => $this->handleCommand($user, $state, $text),
            'waiting_mood_note' => $this->processMoodNote($user, $state, $text),
            'waiting_overwrite_confirm' => $this->processOverwriteConfirm($user, $state, $text),
            default => $this->handleCommand($user, $state, $text),
        };
    }

    private function handleCallback(User $user, TelegramBotState $state, array $callbackQuery): void
    {
        $data = $callbackQuery['data'] ?? '';

        if (str_starts_with($data, 'mood_score_')) {
            $score = (int) str_replace('mood_score_', '', $data);
            if ($score >= 1 && $score <= 9) {
                $this->processMoodScore($user, $state, $score);
            }
        }

        if ($data === 'overwrite_yes') {
            $this->processOverwriteConfirm($user, $state, 'yes');
        }

        if ($data === 'overwrite_no') {
            $state->update(['current_state' => 'idle', 'context' => []]);
            $this->sendMessage($user->telegram_id, "Got it! Your previous mood entry was kept.");
        }
    }

    private function handleCommand(User $user, TelegramBotState $state, string $text): void
    {
        match ($text) {
            '/start', '/menu' => $this->sendMainMenu($user->telegram_id),
            '/mood'           => $this->promptMoodScore($user->telegram_id, $state),
            '/help'           => $this->sendMessage($user->telegram_id, "Available commands:\n/mood - Record your mood\n/menu - Main menu"),
            default           => $this->sendMainMenu($user->telegram_id),
        };
    }

    private function promptMoodScore(string $telegramId, TelegramBotState $state): void
    {
        $state->update(['current_state' => 'waiting_mood_score']);

        // Build inline keyboard with scores 1-9
        $keyboard = [];
        $row = [];
        for ($i = 1; $i <= 9; $i++) {
            $row[] = ['text' => (string) $i, 'callback_data' => "mood_score_{$i}"];
            if ($i % 3 === 0) {
                $keyboard[] = $row;
                $row = [];
            }
        }

        $this->sendInlineKeyboard($telegramId, "How are you feeling right now? (1 = very bad, 9 = excellent)", $keyboard);
    }

    private function processMoodScore(User $user, TelegramBotState $state, int $score): void
    {
        $result = $this->submitMoodAction->execute($user, $score, null, MoodChannel::Telegram);

        if ($result['status'] === 'cooldown') {
            $state->update([
                'current_state' => 'waiting_overwrite_confirm',
                'context' => ['pending_score' => $score, 'existing_entry_id' => $result['recent_entry']->id],
            ]);

            $keyboard = [
                [
                    ['text' => '✅ Yes, update it', 'callback_data' => 'overwrite_yes'],
                    ['text' => '❌ No, keep it', 'callback_data' => 'overwrite_no'],
                ]
            ];

            $this->sendInlineKeyboard(
                $user->telegram_id,
                "You already submitted a mood entry recently. Would you like to update it to {$score}?",
                $keyboard
            );
            return;
        }

        $state->update([
            'current_state' => 'waiting_mood_note',
            'context'       => ['mood_entry_id' => $result['entry']->id, 'score' => $score],
        ]);

        $this->sendMessage($user->telegram_id, "Score {$score} recorded! ✅\n\nWould you like to add a note? (Send your note or /skip to finish)");
    }

    private function processMoodNote(User $user, TelegramBotState $state, string $text): void
    {
        if ($text === '/skip') {
            $state->update(['current_state' => 'idle', 'context' => []]);
            $this->sendMessage($user->telegram_id, "Mood entry saved! 🌟");
            return;
        }

        $entryId = $state->context['mood_entry_id'] ?? null;
        if ($entryId) {
            \App\Models\MoodEntry::where('id', $entryId)->update(['note' => $text]);
        }

        $state->update(['current_state' => 'idle', 'context' => []]);
        $this->sendMessage($user->telegram_id, "Mood entry with note saved! 🌟");
    }

    private function processOverwriteConfirm(User $user, TelegramBotState $state, string $text): void
    {
        if (strtolower($text) === 'yes' || $text === 'overwrite_yes') {
            $context = $state->context ?? [];
            $existingEntry = \App\Models\MoodEntry::find($context['existing_entry_id'] ?? null);

            if ($existingEntry) {
                $this->submitMoodAction->overwrite($user, $existingEntry, $context['pending_score'], null, MoodChannel::Telegram);
                $this->sendMessage($user->telegram_id, "Mood updated to {$context['pending_score']}! ✅");
            }
        } else {
            $this->sendMessage($user->telegram_id, "Okay, keeping your previous entry.");
        }

        $state->update(['current_state' => 'idle', 'context' => []]);
    }

    private function sendMainMenu(string $telegramId): void
    {
        $keyboard = [
            [['text' => '😊 Record Mood', 'callback_data' => 'menu_mood']],
        ];
        $this->sendInlineKeyboard($telegramId, "Welcome to PsychoCare Bot! What would you like to do?", $keyboard);
    }

    private function sendMessage(string $telegramId, string $text): void
    {
        $this->callTelegramApi('sendMessage', [
            'chat_id' => $telegramId,
            'text'    => $text,
        ]);
    }

    private function sendInlineKeyboard(string $telegramId, string $text, array $keyboard): void
    {
        $this->callTelegramApi('sendMessage', [
            'chat_id'      => $telegramId,
            'text'         => $text,
            'reply_markup' => json_encode(['inline_keyboard' => $keyboard]),
        ]);
    }

    private function callTelegramApi(string $method, array $params): void
    {
        $token = config('services.telegram.bot_token');
        if (!$token) {
            Log::warning("Telegram bot token not configured");
            return;
        }

        try {
            \Illuminate\Support\Facades\Http::timeout(5)
                ->post("https://api.telegram.org/bot{$token}/{$method}", $params);
        } catch (\Throwable $e) {
            Log::warning("Telegram API call failed: {$e->getMessage()}");
        }
    }
}
