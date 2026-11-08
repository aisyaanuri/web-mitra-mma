<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class RoleUserSeeder extends Seeder
{
    /**
     * Seed one demo account per role.
     */
    public function run(): void
    {
        $accounts = [
            ['name' => 'Owner Demo', 'email' => 'owner@mustika.test', 'role' => UserRole::Owner],
            ['name' => 'Admin Gudang Demo', 'email' => 'gudang@mustika.test', 'role' => UserRole::AdminGudang],
            ['name' => 'Admin Marketplace Demo', 'email' => 'marketplace@mustika.test', 'role' => UserRole::AdminMarketplace],
        ];

        foreach ($accounts as $account) {
            User::factory()->role($account['role'])->create([
                'name' => $account['name'],
                'email' => $account['email'],
            ]);
        }
    }
}
