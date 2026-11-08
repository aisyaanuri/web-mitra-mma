<?php

namespace Tests\Unit\Models;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class UserActivityLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_a_user_is_logged(): void
    {
        $user = User::factory()->create(['name' => 'Budi Baru']);

        $this->assertTrue(
            Activity::query()->where('subject_id', $user->id)->where('event', 'created')->exists()
        );
    }

    public function test_role_change_is_logged_with_old_and_new_values(): void
    {
        $user = User::factory()->role(UserRole::AdminGudang)->create();

        $user->update(['role' => UserRole::AdminMarketplace]);

        $activity = Activity::query()
            ->where('subject_id', $user->id)
            ->where('event', 'updated')
            ->latest('id')
            ->first();

        $this->assertNotNull($activity);
        $this->assertSame(UserRole::AdminGudang->value, $activity->attribute_changes['old']['role']);
        $this->assertSame(UserRole::AdminMarketplace->value, $activity->attribute_changes['attributes']['role']);
    }

    public function test_password_changes_are_never_recorded_in_the_activity_log(): void
    {
        $user = User::factory()->create();

        $user->forceFill(['password' => 'a-brand-new-password'])->save();

        $activity = Activity::query()
            ->where('subject_id', $user->id)
            ->where('event', 'updated')
            ->latest('id')
            ->first();

        // Password is not in logOnly(), so an update touching only the
        // password should produce no loggable changes at all.
        $this->assertNull($activity);
    }

    public function test_deactivating_a_user_is_logged(): void
    {
        $user = User::factory()->create(['is_active' => true]);

        $user->update(['is_active' => false]);

        $this->assertTrue(
            Activity::query()
                ->where('subject_id', $user->id)
                ->where('event', 'updated')
                ->get()
                ->contains(fn ($activity) => ($activity->attribute_changes['attributes']['is_active'] ?? null) === false)
        );
    }
}
