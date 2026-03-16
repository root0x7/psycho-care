<?php

namespace Database\Seeders;

use App\Models\MoodScoreDefault;
use Illuminate\Database\Seeder;

class MoodScoreDefaultsSeeder extends Seeder
{
    private array $defaults = [
        1 => ['label' => 'Terrible', 'description' => 'Feeling extremely bad, can barely function.'],
        2 => ['label' => 'Very Bad', 'description' => 'Feeling very low, significant distress.'],
        3 => ['label' => 'Bad', 'description' => 'Feeling quite bad, many difficulties.'],
        4 => ['label' => 'Below Average', 'description' => 'Feeling below normal, some difficulties.'],
        5 => ['label' => 'Neutral', 'description' => 'Feeling neutral, neither good nor bad.'],
        6 => ['label' => 'Okay', 'description' => 'Feeling reasonably okay, minor issues.'],
        7 => ['label' => 'Good', 'description' => 'Feeling good, doing well.'],
        8 => ['label' => 'Very Good', 'description' => 'Feeling very good, high energy.'],
        9 => ['label' => 'Excellent', 'description' => 'Feeling excellent, at best.'],
    ];

    public function run(): void
    {
        foreach (['en', 'ru', 'uz'] as $locale) {
            foreach ($this->defaults as $score => $data) {
                MoodScoreDefault::firstOrCreate(
                    ['score' => $score, 'locale' => $locale],
                    ['label' => $data['label'], 'description' => $data['description']]
                );
            }
        }
    }
}
