<?php

namespace Tests\Feature\Dashboard;

use App\Enums\UserRole;
use App\Enums\WarehouseStatus;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_roles_can_view_the_dashboard(): void
    {
        foreach (UserRole::cases() as $role) {
            $user = User::factory()->role($role)->create();

            $this->actingAs($user)
                ->get(route('dashboard'))
                ->assertOk();
        }
    }

    public function test_shows_correct_order_count_and_warehouse_breakdown(): void
    {
        Order::factory()->create(['warehouse_status' => WarehouseStatus::Diterima]);
        Order::factory()->create(['warehouse_status' => WarehouseStatus::Dikirim]);
        Order::factory()->create(['warehouse_status' => null]);

        $owner = User::factory()->role(UserRole::Owner)->create();

        $component = Livewire::actingAs($owner)->test('pages::dashboard');

        $this->assertSame(3, $component->get('totalOrders'));
        $this->assertSame(1, $component->get('warehouseBreakdown')[WarehouseStatus::Diterima->value]);
        $this->assertSame(1, $component->get('warehouseBreakdown')[WarehouseStatus::Dikirim->value]);
        $this->assertSame(1, $component->get('warehouseBreakdown')['belum_diproses']);
    }

    public function test_admin_gudang_does_not_see_sales_chart_or_reconciliation(): void
    {
        $user = User::factory()->role(UserRole::AdminGudang)->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('Penjualan 6 Bulan Terakhir')
            ->assertDontSee('Ringkasan Rekonsiliasi');
    }

    public function test_owner_sees_sales_chart_and_reconciliation_summary(): void
    {
        $owner = User::factory()->role(UserRole::Owner)->create();

        $this->actingAs($owner)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Penjualan 6 Bulan Terakhir')
            ->assertSee('Ringkasan Rekonsiliasi');
    }
}
