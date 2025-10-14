<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create permissions
        $permissions = [
            // User management
            ['name' => 'view_users', 'display_name' => 'View Users', 'description' => 'Can view user list and details'],
            ['name' => 'create_users', 'display_name' => 'Create Users', 'description' => 'Can create new users'],
            ['name' => 'edit_users', 'display_name' => 'Edit Users', 'description' => 'Can edit user information'],
            ['name' => 'delete_users', 'display_name' => 'Delete Users', 'description' => 'Can delete users'],
            ['name' => 'manage_roles', 'display_name' => 'Manage Roles', 'description' => 'Can assign and manage user roles'],

            // Announcement management
            ['name' => 'view_announcements', 'display_name' => 'View Announcements', 'description' => 'Can view announcements'],
            ['name' => 'create_announcements', 'display_name' => 'Create Announcements', 'description' => 'Can create announcements'],
            ['name' => 'edit_announcements', 'display_name' => 'Edit Announcements', 'description' => 'Can edit announcements'],
            ['name' => 'delete_announcements', 'display_name' => 'Delete Announcements', 'description' => 'Can delete announcements'],
            ['name' => 'publish_announcements', 'display_name' => 'Publish Announcements', 'description' => 'Can publish announcements'],

            // Event management
            ['name' => 'view_events', 'display_name' => 'View Events', 'description' => 'Can view events'],
            ['name' => 'create_events', 'display_name' => 'Create Events', 'description' => 'Can create events'],
            ['name' => 'edit_events', 'display_name' => 'Edit Events', 'description' => 'Can edit events'],
            ['name' => 'delete_events', 'display_name' => 'Delete Events', 'description' => 'Can delete events'],
            ['name' => 'manage_event_attendance', 'display_name' => 'Manage Event Attendance', 'description' => 'Can manage event attendance'],

            // Message management
            ['name' => 'send_messages', 'display_name' => 'Send Messages', 'description' => 'Can send messages'],
            ['name' => 'view_messages', 'display_name' => 'View Messages', 'description' => 'Can view messages'],
            ['name' => 'manage_message_groups', 'display_name' => 'Manage Message Groups', 'description' => 'Can create and manage message groups'],

            // Finance management
            ['name' => 'view_contributions', 'display_name' => 'View Contributions', 'description' => 'Can view contributions'],
            ['name' => 'create_contributions', 'display_name' => 'Create Contributions', 'description' => 'Can record contributions'],
            ['name' => 'edit_contributions', 'display_name' => 'Edit Contributions', 'description' => 'Can edit contributions'],
            ['name' => 'view_donations', 'display_name' => 'View Donations', 'description' => 'Can view donations'],
            ['name' => 'create_donations', 'display_name' => 'Create Donations', 'description' => 'Can record donations'],
            ['name' => 'edit_donations', 'display_name' => 'Edit Donations', 'description' => 'Can edit donations'],
            ['name' => 'view_financial_reports', 'display_name' => 'View Financial Reports', 'description' => 'Can view financial reports'],

            // Ministry management
            ['name' => 'view_ministries', 'display_name' => 'View Ministries', 'description' => 'Can view ministries'],
            ['name' => 'create_ministries', 'display_name' => 'Create Ministries', 'description' => 'Can create ministries'],
            ['name' => 'edit_ministries', 'display_name' => 'Edit Ministries', 'description' => 'Can edit ministries'],
            ['name' => 'manage_ministry_members', 'display_name' => 'Manage Ministry Members', 'description' => 'Can manage ministry members'],

            // Sermon and Teaching
            ['name' => 'view_sermons', 'display_name' => 'View Sermons', 'description' => 'Can view sermons'],
            ['name' => 'create_sermons', 'display_name' => 'Create Sermons', 'description' => 'Can create sermons'],
            ['name' => 'edit_sermons', 'display_name' => 'Edit Sermons', 'description' => 'Can edit sermons'],
            ['name' => 'view_teaching_notes', 'display_name' => 'View Teaching Notes', 'description' => 'Can view teaching notes'],
            ['name' => 'create_teaching_notes', 'display_name' => 'Create Teaching Notes', 'description' => 'Can create teaching notes'],
            ['name' => 'edit_teaching_notes', 'display_name' => 'Edit Teaching Notes', 'description' => 'Can edit teaching notes'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name']],
                $permission
            );
        }

        // Create roles
        $roles = [
            [
                'name' => 'member',
                'display_name' => 'Member',
                'description' => 'Regular church member',
                'permissions' => [
                    'view_announcements',
                    'view_events',
                    'send_messages',
                    'view_messages',
                    'view_sermons',
                    'view_teaching_notes',
                ]
            ],
            [
                'name' => 'pastor',
                'display_name' => 'Pastor',
                'description' => 'Church pastor with ministry management capabilities',
                'permissions' => [
                    'view_announcements',
                    'create_announcements',
                    'edit_announcements',
                    'delete_announcements',
                    'publish_announcements',
                    'view_events',
                    'create_events',
                    'edit_events',
                    'delete_events',
                    'manage_event_attendance',
                    'send_messages',
                    'view_messages',
                    'manage_message_groups',
                    'view_ministries',
                    'create_ministries',
                    'edit_ministries',
                    'manage_ministry_members',
                    'view_sermons',
                    'create_sermons',
                    'edit_sermons',
                    'view_teaching_notes',
                    'create_teaching_notes',
                    'edit_teaching_notes',
                ]
            ],
            [
                'name' => 'finance_committee',
                'display_name' => 'Finance Committee',
                'description' => 'Finance committee member with financial management capabilities',
                'permissions' => [
                    'view_announcements',
                    'create_announcements',
                    'edit_announcements',
                    'delete_announcements',
                    'publish_announcements',
                    'view_events',
                    'send_messages',
                    'view_messages',
                    'view_contributions',
                    'create_contributions',
                    'edit_contributions',
                    'view_donations',
                    'create_donations',
                    'edit_donations',
                    'view_financial_reports',
                ]
            ],
            [
                'name' => 'administrator',
                'display_name' => 'Administrator',
                'description' => 'System administrator with full access',
                'permissions' => array_column($permissions, 'name') // All permissions
            ]
        ];

        foreach ($roles as $roleData) {
            $permissions = $roleData['permissions'];
            unset($roleData['permissions']);
            
            $role = Role::firstOrCreate(
                ['name' => $roleData['name']],
                $roleData
            );
            
            // Sync permissions (this will add/remove as needed)
            $role->permissions()->sync(
                Permission::whereIn('name', $permissions)->pluck('id')
            );
        }
    }
}
