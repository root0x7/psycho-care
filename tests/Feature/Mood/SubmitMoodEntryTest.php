<?php

namespace Tests\Feature\Mood;

use App\Actions\Mood\SubmitMoodEntryAction;
use App\Enums\MoodChannel;
use App\Enums\Role;
use App\Models\MoodEntry;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubmitMoodEntryTest extends TestCase
{
    use RefreshDatabase;

    private User $patient;
    private SubmitMoodEntryAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure roles exist (normally done by seeder)
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => Role::Patient->value, 'guard_name' => 'web']);

        $this->patient = User::factory()->create(['timezone' => 'UTC', 'is_active' => true]);
        $this->patient->assignRole(Role::Patient->value);
        $this->action = app(SubmitMoodEntryAction::class);
    }

    public function test_creates_mood_entry_successfully(): void
    {
        $result = $this->action->execute($this->patient, 7, 'Feeling good today', MoodChannel::Web);

        $this->assertSame('created', $result['status']);
        $this->assertInstanceOf(MoodEntry::class, $result['entry']);
        $this->assertSame(7, $result['entry']->score);
        $this->assertSame('Feeling good today', $result['entry']->note);
        $this->assertSame('web', $result['entry']->channel->value);
    }

    public function test_returns_cooldown_when_entry_within_one_hour(): void
    {
        MoodEntry::factory()->create([
            'user_id'      => $this->patient->id,
            'score'        => 6,
            'submitted_at' => Carbon::now()->subMinutes(30),
        ]);

        $result = $this->action->execute($this->patient, 8, null, MoodChannel::Web);

        $this->assertSame('cooldown', $result['status']);
        $this->assertTrue($result['can_overwrite']);
    }

    public function test_allows_new_entry_after_cooldown_expires(): void
    {
        MoodEntry::factory()->create([
            'user_id'      => $this->patient->id,
            'score'        => 5,
            'submitted_at' => Carbon::now()->subMinutes(61),
        ]);

        $result = $this->action->execute($this->patient, 8, 'Fresh entry', MoodChannel::Web);

        $this->assertSame('created', $result['status']);
    }

    public function test_overwrite_preserves_revision_history(): void
    {
        $existing = MoodEntry::factory()->create([
            'user_id'      => $this->patient->id,
            'score'        => 4,
            'note'         => 'Original note',
            'submitted_at' => Carbon::now()->subMinutes(10),
        ]);

        $updated = $this->action->overwrite($this->patient, $existing, 8, 'Updated note', MoodChannel::Web);

        $this->assertSame(8, $updated->score);
        $this->assertSame('Updated note', $updated->note);
        $this->assertTrue($updated->was_overwritten);

        $this->assertDatabaseHas('mood_entry_revisions', [
            'mood_entry_id'  => $existing->id,
            'previous_score' => 4,
            'previous_note'  => 'Original note',
            'new_score'      => 8,
        ]);
    }
}
