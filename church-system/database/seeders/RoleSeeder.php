<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'user',
                'display_name' => 'Member',
                'description' => 'Regular church member with basic access'
            ],
            [
                'name' => 'pastor',
                'display_name' => 'Pastor',
                'description' => 'Pastor with ministry management and communication privileges'
            ],
            [
                'name' => 'finance_committee',
                'display_name' => 'Finance Committee',
                'description' => 'Finance committee member with contribution management access'
            ],
            [
                'name' => 'administrator',
                'display_name' => 'Administrator',
                'description' => 'Full system administrator with complete access'
            ]
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(
                ['name' => $role['name']],
                $role
            );
        }
    }
}
