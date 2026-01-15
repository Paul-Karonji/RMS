<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $tenant = \App\Models\Tenant::factory()->create();
        $this->user = User::factory()->create(['tenant_id' => $tenant->id]);
    }

    /** @test */
    public function user_can_list_their_notifications()
    {
        Notification::factory()->count(5)->create([
            'user_id' => $this->user->id,
            'tenant_id' => $this->user->tenant_id,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/notifications');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => ['id', 'type', 'title', 'message', 'status', 'read_at', 'created_at']
                ],
                'meta' => ['current_page', 'last_page', 'per_page', 'total']
            ])
            ->assertJsonCount(5, 'data');
    }

    /** @test */
    public function user_can_mark_notification_as_read()
    {
        $notification = Notification::factory()->create([
            'user_id' => $this->user->id,
            'tenant_id' => $this->user->tenant_id,
            'read_at' => null,
        ]);

        $response = $this->actingAs($this->user)
            ->patchJson("/api/notifications/{$notification->id}/read");

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $notification->refresh();
        $this->assertNotNull($notification->read_at);
    }

    /** @test */
    public function user_can_get_unread_count()
    {
        Notification::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'tenant_id' => $this->user->tenant_id,
            'read_at' => null,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/notifications/unread-count');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => ['unread_count' => 3]
            ]);
    }

    /** @test */
    public function user_cannot_access_other_users_notifications()
    {
        $otherUser = User::factory()->create([
            'tenant_id' => $this->user->tenant_id,
        ]);
        
        $notification = Notification::factory()->create([
            'user_id' => $otherUser->id,
            'tenant_id' => $this->user->tenant_id,
        ]);

        $response = $this->actingAs($this->user)
            ->patchJson("/api/notifications/{$notification->id}/read");

        $response->assertStatus(404);
    }

    /** @test */
    public function user_can_mark_all_notifications_as_read()
    {
        Notification::factory()->count(5)->create([
            'user_id' => $this->user->id,
            'tenant_id' => $this->user->tenant_id,
            'read_at' => null,
        ]);

        $response = $this->actingAs($this->user)
            ->patchJson('/api/notifications/read-all');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertEquals(0, Notification::where('user_id', $this->user->id)
            ->whereNull('read_at')
            ->count());
    }

    /** @test */
    public function user_can_delete_notification()
    {
        $notification = Notification::factory()->create([
            'user_id' => $this->user->id,
            'tenant_id' => $this->user->tenant_id,
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/notifications/{$notification->id}");

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('notifications', ['id' => $notification->id]);
    }

    /** @test */
    public function user_can_filter_notifications_by_status()
    {
        Notification::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'tenant_id' => $this->user->tenant_id,
            'read_at' => null,
        ]);

        Notification::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'tenant_id' => $this->user->tenant_id,
            'read_at' => now(),
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/notifications?status=unread');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function user_can_get_recent_notifications()
    {
        Notification::factory()->count(15)->create([
            'user_id' => $this->user->id,
            'tenant_id' => $this->user->tenant_id,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/notifications/recent?limit=5');

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data');
    }
}
