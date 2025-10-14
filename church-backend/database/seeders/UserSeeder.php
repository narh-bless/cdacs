<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Ministry;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@church.com'],
            [
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@church.com',
            'password' => Hash::make('password'),
            'phone' => '+1234567890',
            'membership_date' => now()->subYears(5),
            'membership_status' => 'active',
            'is_active' => true,
            ]
        );

        $admin->roles()->syncWithoutDetaching([Role::where('name', 'administrator')->first()->id]);

        // Create pastor user
        $pastor = User::firstOrCreate(
            ['email' => 'pastor@church.com'],
            [
            'first_name' => 'John',
            'last_name' => 'Pastor',
            'email' => 'pastor@church.com',
            'password' => Hash::make('password'),
            'phone' => '+1234567891',
            'membership_date' => now()->subYears(10),
            'membership_status' => 'active',
            'is_active' => true,
            'bio' => 'Senior Pastor with over 10 years of ministry experience.',
            ]
        );

        $pastor->roles()->syncWithoutDetaching([Role::where('name', 'pastor')->first()->id]);

        // Create finance committee member
        $financeMember = User::firstOrCreate(
            ['email' => 'finance@church.com'],
            [
                'first_name' => 'Sarah',
                'last_name' => 'Finance',
                'email' => 'finance@church.com',
                'password' => Hash::make('password'),
                'phone' => '+1234567892',
                'membership_date' => now()->subYears(3),
                'membership_status' => 'active',
                'is_active' => true,
                'bio' => 'Finance Committee member responsible for financial oversight.',
            ]
        );

        $financeMember->roles()->syncWithoutDetaching([Role::where('name', 'finance_committee')->first()->id]);

        // Create regular members
        $members = [
            [
                'first_name' => 'Michael',
                'last_name' => 'Johnson',
                'email' => 'michael@church.com',
                'phone' => '+1234567893',
                'bio' => 'Active member of the worship team.',
            ],
            [
                'first_name' => 'Emily',
                'last_name' => 'Davis',
                'email' => 'emily@church.com',
                'phone' => '+1234567894',
                'bio' => 'Youth ministry volunteer.',
            ],
            [
                'first_name' => 'David',
                'last_name' => 'Wilson',
                'email' => 'david@church.com',
                'phone' => '+1234567895',
                'bio' => 'Sunday school teacher.',
            ],
            [
                'first_name' => 'Lisa',
                'last_name' => 'Brown',
                'email' => 'lisa@church.com',
                'phone' => '+1234567896',
                'bio' => 'Community outreach coordinator.',
            ],
            [
                'first_name' => 'Robert',
                'last_name' => 'Miller',
                'email' => 'robert@church.com',
                'phone' => '+1234567897',
                'bio' => 'Church maintenance volunteer.',
            ],
        ];

        foreach ($members as $memberData) {
            $member = User::firstOrCreate(
                ['email' => $memberData['email']],
                [
                    'first_name' => $memberData['first_name'],
                    'last_name' => $memberData['last_name'],
                    'email' => $memberData['email'],
                    'password' => Hash::make('password'),
                    'phone' => $memberData['phone'],
                    'membership_date' => now()->subMonths(rand(1, 24)),
                    'membership_status' => 'active',
                    'is_active' => true,
                    'bio' => $memberData['bio'],
                ]
            );

            $member->roles()->syncWithoutDetaching([Role::where('name', 'member')->first()->id]);
        }

        // Create ministries
        $ministries = [
            [
                'name' => 'Worship Team',
                'description' => 'Responsible for leading worship during services',
                'leader_name' => 'Michael Johnson',
                'leader_id' => User::where('email', 'michael@church.com')->first()->id,
            ],
            [
                'name' => 'Youth Ministry',
                'description' => 'Ministry focused on youth and young adults',
                'leader_name' => 'Emily Davis',
                'leader_id' => User::where('email', 'emily@church.com')->first()->id,
            ],
            [
                'name' => 'Sunday School',
                'description' => 'Educational ministry for all ages',
                'leader_name' => 'David Wilson',
                'leader_id' => User::where('email', 'david@church.com')->first()->id,
            ],
            [
                'name' => 'Community Outreach',
                'description' => 'Ministry focused on serving the community',
                'leader_name' => 'Lisa Brown',
                'leader_id' => User::where('email', 'lisa@church.com')->first()->id,
            ],
            [
                'name' => 'Maintenance Team',
                'description' => 'Responsible for church building maintenance',
                'leader_name' => 'Robert Miller',
                'leader_id' => User::where('email', 'robert@church.com')->first()->id,
            ],
        ];

        foreach ($ministries as $ministryData) {
            $ministry = Ministry::firstOrCreate(
                ['name' => $ministryData['name']],
                $ministryData
            );
            
            // Add leader as ministry member (only if not already attached)
            if (!$ministry->members()->where('user_id', $ministryData['leader_id'])->exists()) {
                $ministry->members()->attach($ministryData['leader_id'], [
                    'role' => 'leader',
                    'joined_date' => now()->subMonths(rand(6, 18)),
                ]);
            }

            // Add some additional members to each ministry
            $additionalMembers = User::where('email', '!=', $ministryData['leader_id'])
                ->whereHas('roles', function ($query) {
                    $query->where('name', 'member');
                })
                ->inRandomOrder()
                ->limit(rand(2, 4))
                ->get();

            foreach ($additionalMembers as $member) {
                if (!$ministry->members()->where('user_id', $member->id)->exists()) {
                    $ministry->members()->attach($member->id, [
                        'role' => 'member',
                        'joined_date' => now()->subMonths(rand(1, 12)),
                    ]);
                }
            }
        }
    }
}
