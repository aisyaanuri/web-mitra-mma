<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\SyncLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SyncStatusPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_gudang_cannot_access_sync_status(): void
    {
        $user = User::factory()->role(UserRole::AdminGudang)->create();

        $this->actingAs($user)
            ->get(route('admin.sync.index'))
            ->assertForbidden();
    }

    public function test_owner_and_admin_marketplace_can_view_sync_status(): void
    {
        $owner = User::factory()->role(UserRole::Owner)->create();
        $marketplace = User::factory()->role(UserRole::AdminMarketplace)->create();

        $this->actingAs($owner)->get(route('admin.sync.index'))->assertOk();
        $this->actingAs($marketplace)->get(route('admin.sync.index'))->assertOk();
    }

    public function test_sync_now_button_runs_the_sync_jobs_and_logs_results(): void
    {
        $owner = User::factory()->role(UserRole::Owner)->create();

        Livewire::actingAs($owner)
            ->test('pages::admin.sync.index')
            ->call('syncNow');

        $this->assertTrue(SyncLog::where('job_type', 'shopee_orders')->exists());
        $this->assertTrue(SyncLog::where('job_type', 'shopee_logistics')->exists());
        $this->assertTrue(SyncLog::where('job_type', 'shopee_returns')->exists());
    }

    public function test_shows_fake_mode_warning_by_default(): void
    {
        $owner = User::factory()->role(UserRole::Owner)->create();

        $this->actingAs($owner)
            ->get(route('admin.sync.index'))
            ->assertSee('Fake (Data Simulasi)');
    }
}
