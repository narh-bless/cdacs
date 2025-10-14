<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Announcement;
use Tymon\JWTAuth\Facades\JWTAuth;

class AnnouncementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create roles
        $this->createRoles();
    }

    private function createRoles(): void
    {
        Role::create(['name' => 'member', 'display_name' => 'Member']);
        Role::create(['name' => 'pastor', 'display_name' => 'Pastor']);
        Role::create(['name' => 'administrator', 'display_name' => 'Administrator']);
    }

    /**
     * Test getting announcements list.
     */
    public function test_can_get_announcements(): void
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::where('name', 'member')->first()->id);

        $announcement = Announcement::create([
            'title' => 'Test Announcement',
            'content' => 'This is a test announcement',
            'type' => 'general',
            'priority' => 'medium',
            'is_published' => true,
            'published_at' => now(),
            'author_id' => $user->id,
        ]);

        $token = JWTAuth::fromUser($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/announcements');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'id',
                            'title',
                            'content',
                            'type',
                            'priority',
                            'is_published',
                            'author',
                        ]
                    ]
                ]);
    }

    /**
     * Test creating announcement as pastor.
     */
    public function test_pastor_can_create_announcement(): void
    {
        $pastor = User::factory()->create();
        $pastor->roles()->attach(Role::where('name', 'pastor')->first()->id);

        $token = JWTAuth::fromUser($pastor);

        $announcementData = [
            'title' => 'New Announcement',
            'content' => 'This is a new announcement',
            'type' => 'general',
            'priority' => 'medium',
            'is_published' => false,
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/announcements', $announcementData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'message',
                    'announcement' => [
                        'id',
                        'title',
                        'content',
                        'type',
                        'priority',
                        'author',
                    ]
                ]);

        $this->assertDatabaseHas('announcements', [
            'title' => 'New Announcement',
            'content' => 'This is a new announcement',
            'author_id' => $pastor->id,
        ]);
    }

    /**
     * Test member cannot create announcement.
     */
    public function test_member_cannot_create_announcement(): void
    {
        $member = User::factory()->create();
        $member->roles()->attach(Role::where('name', 'member')->first()->id);

        $token = JWTAuth::fromUser($member);

        $announcementData = [
            'title' => 'Unauthorized Announcement',
            'content' => 'This should not be allowed',
            'type' => 'general',
            'priority' => 'medium',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/announcements', $announcementData);

        $response->assertStatus(403);
    }

    /**
     * Test getting specific announcement.
     */
    public function test_can_get_specific_announcement(): void
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::where('name', 'member')->first()->id);

        $announcement = Announcement::create([
            'title' => 'Specific Announcement',
            'content' => 'This is a specific announcement',
            'type' => 'general',
            'priority' => 'medium',
            'is_published' => true,
            'published_at' => now(),
            'author_id' => $user->id,
        ]);

        $token = JWTAuth::fromUser($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/announcements/{$announcement->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'id' => $announcement->id,
                    'title' => 'Specific Announcement',
                    'content' => 'This is a specific announcement',
                ]);
    }
}
