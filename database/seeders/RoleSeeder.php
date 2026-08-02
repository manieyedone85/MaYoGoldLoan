<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\LoanApprovalLimit;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['code' => 'CUSTOMER', 'name' => 'Customer'],
            ['code' => 'BRANCH_EXECUTIVE', 'name' => 'Branch Executive'],
            ['code' => 'APPRAISER', 'name' => 'Gold Appraiser'],
            ['code' => 'CASHIER', 'name' => 'Cashier'],
            ['code' => 'BRANCH_MANAGER', 'name' => 'Branch Manager', 'limit' => 200000],
            ['code' => 'REGIONAL_MANAGER', 'name' => 'Regional Manager', 'limit' => 1000000],
            ['code' => 'OPERATIONS', 'name' => 'Operations Team'],
            ['code' => 'FINANCE', 'name' => 'Finance Team'],
            ['code' => 'AUDITOR', 'name' => 'Auditor'],
            ['code' => 'ADMIN', 'name' => 'Admin'],
        ];

        foreach ($roles as $roleData) {
            $limit = $roleData['limit'] ?? null;
            unset($roleData['limit']);

            $role = Role::firstOrCreate(['code' => $roleData['code']], $roleData);

            if ($limit) {
                LoanApprovalLimit::firstOrCreate(['role_id' => $role->id], ['max_amount' => $limit]);
            }
        }
    }
}
