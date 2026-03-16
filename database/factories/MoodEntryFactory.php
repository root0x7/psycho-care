<?php

namespace Database\Factories;

use App\Enums\MoodChannel;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class MoodEntryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'           => User::factory(),
            'score'             => fake()->numberBetween(1, 9),
            'note'              => fake()->optional()->sentence(),
            'channel'           => MoodChannel::Web->value,
            'submitted_at'      => Carbon::now()->subHours(fake()->numberBetween(2, 48)),
            'user_timezone'     => 'UTC',
            'local_submitted_at' => Carbon::now()->subHours(fake()->numberBetween(2, 48)),
            'was_overwritten'   => false,
        ];
    }
}
