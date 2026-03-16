<?php

namespace Tests\Feature\Risk;

use App\Enums\RiskLevel;
use App\Enums\RiskKeywordStatus;
use App\Models\MoodEntry;
use App\Models\RiskKeyword;
use App\Models\User;
use App\Services\Risk\RiskKeywordDetectionService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RiskKeywordDetectionTest extends TestCase
{
    use RefreshDatabase;

    private RiskKeywordDetectionService $service;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(RiskKeywordDetectionService::class);
        $this->user    = User::factory()->create(['locale' => 'en']);

        RiskKeyword::create([
            'phrase'          => 'kill myself',
            'locale'          => 'en',
            'severity'        => RiskLevel::Critical->value,
            'status'          => RiskKeywordStatus::Approved->value,
            'is_phrase_match' => true,
        ]);

        RiskKeyword::create([
            'phrase'          => 'hopeless',
            'locale'          => 'en',
            'severity'        => RiskLevel::Medium->value,
            'status'          => RiskKeywordStatus::Approved->value,
            'is_phrase_match' => false,
        ]);

        RiskKeyword::create([
            'phrase'          => 'sad',
            'locale'          => 'en',
            'severity'        => RiskLevel::Low->value,
            'status'          => RiskKeywordStatus::Approved->value,
            'is_phrase_match' => false,
        ]);
    }

    public function test_detects_critical_keyword_in_mood_note(): void
    {
        $entry = MoodEntry::factory()->create([
            'user_id'      => $this->user->id,
            'note'         => 'I feel like I want to kill myself tonight',
            'score'        => 1,
            'submitted_at' => Carbon::now(),
        ]);

        $matches = $this->service->analyze($entry);

        $this->assertNotEmpty($matches);
        $this->assertSame(RiskLevel::Critical, $matches->first()['severity']);
    }

    public function test_persists_matches_to_database(): void
    {
        $entry = MoodEntry::factory()->create([
            'user_id'      => $this->user->id,
            'note'         => 'Everything feels hopeless and I feel so sad',
            'score'        => 2,
            'submitted_at' => Carbon::now(),
        ]);

        $this->service->analyze($entry);

        $this->assertDatabaseHas('risk_keyword_matches', [
            'mood_entry_id' => $entry->id,
        ]);
    }

    public function test_returns_empty_when_note_is_null(): void
    {
        $entry = MoodEntry::factory()->create([
            'user_id'      => $this->user->id,
            'note'         => null,
            'score'        => 5,
            'submitted_at' => Carbon::now(),
        ]);

        $matches = $this->service->analyze($entry);

        $this->assertEmpty($matches);
    }

    public function test_higher_severity_wins_on_overlapping_spans(): void
    {
        RiskKeyword::create([
            'phrase'          => 'hopeless feeling',
            'locale'          => 'en',
            'severity'        => RiskLevel::Critical->value,
            'status'          => RiskKeywordStatus::Approved->value,
            'is_phrase_match' => true,
        ]);

        $entry = MoodEntry::factory()->create([
            'user_id'      => $this->user->id,
            'note'         => 'I have a hopeless feeling today',
            'score'        => 2,
            'submitted_at' => Carbon::now(),
        ]);

        $matches = $this->service->analyze($entry);

        $severities = $matches->pluck('severity')->map(fn ($s) => $s->value)->toArray();
        $this->assertNotContains(RiskLevel::Medium->value, $severities);
    }
}
