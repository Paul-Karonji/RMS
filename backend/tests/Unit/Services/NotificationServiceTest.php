<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Models\User;
use App\Models\Notification;
use App\Services\NotificationService;
use App\Enums\NotificationType;
use Illuminate\Foundation\Testing\RefreshDatabase;

class NotificationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected NotificationService $notificationService;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->notificationService = app(NotificationService::class);
        
        // Create test user with tenant
        $this->user = User::factory()->create([
            'tenant_id' => \App\Models\Tenant::factory()->create()->id,
        ]);
    }

    /** @test */
    public function it_can_create_notification()
    {
        $notification = $this->notificationService->create(
            user: $this->user,
            type: NotificationType::PAYMENT_RECEIVED,
            title: 'Payment Received',
            message: 'Your payment of KES 5000 has been received.',
            data: ['amount' => 5000]
        );

        $this->assertInstanceOf(Notification::class, $notification);
        $this->assertEquals($this->user->id, $notification->user_id);
        $this->assertEquals($this->user->tenant_id, $notification->tenant_id);
        $this->assertEquals('payment_received', $notification->type);
        $this->assertEquals('Payment Received', $notification->title);
        $this->assertEquals('unread', $notification->status);
        $this->assertNull($notification->read_at);
        $this->assertEquals(['amount' => 5000], $notification->data);
    }

    /** @test */
    public function it_can_mark_notification_as_read()
    {
        $notification = Notification::factory()->create([
            'user_id' => $this->user->id,
            'tenant_id' => $this->user->tenant_id,
            'read_at' => null,
        ]);

        $result = $this->notificationService->markAsRead($notification->id, $this->user);

        $this->assertTrue($result);
        $notification->refresh();
        $this->assertNotNull($notification->read_at);
    }

    /** @test */
    public function it_can_get_unread_count()
    {
        // Create 3 unread notifications
        Notification::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'tenant_id' => $this->user->tenant_id,
            'read_at' => null,
        ]);

        // Create 2 read notifications
        Notification::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'tenant_id' => $this->user->tenant_id,
            'read_at' => now(),
        ]);

        $count = $this->notificationService->getUnreadCount($this->user);

        $this->assertEquals(3, $count);
    }

    /** @test */
    public function it_can_get_user_notifications_paginated()
    {
        // Create 25 notifications
        Notification::factory()->count(25)->create([
            'user_id' => $this->user->id,
            'tenant_id' => $this->user->tenant_id,
        ]);

        $notifications = $this->notificationService->getUserNotifications($this->user, perPage: 10);

        $this->assertEquals(10, $notifications->count());
        $this->assertEquals(25, $notifications->total());
        $this->assertEquals(3, $notifications->lastPage());
    }

    /** @test */
    public function it_can_mark_all_as_read()
    {
        // Create 5 unread notifications
        Notification::factory()->count(5)->create([
            'user_id' => $this->user->id,
            'tenant_id' => $this->user->tenant_id,
            'read_at' => null,
        ]);

        $count = $this->notificationService->markAllAsRead($this->user);

        $this->assertEquals(5, $count);
        $this->assertEquals(0, $this->notificationService->getUnreadCount($this->user));
    }

    /** @test */
    public function it_can_delete_notification()
    {
        $notification = Notification::factory()->create([
            'user_id' => $this->user->id,
            'tenant_id' => $this->user->tenant_id,
        ]);

        $result = $this->notificationService->delete($notification->id, $this->user);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('notifications', ['id' => $notification->id]);
    }

    /** @test */
    public function it_can_create_notifications_for_multiple_users()
    {
        $users = User::factory()->count(3)->create([
            'tenant_id' => $this->user->tenant_id,
        ]);

        $notifications = $this->notificationService->createForUsers(
            users: $users,
            type: NotificationType::PROPERTY_APPROVED,
            title: 'Property Approved',
            message: 'Your property has been approved.'
        );

        $this->assertCount(3, $notifications);
        $this->assertEquals(3, Notification::count());
    }

    /** @test */
    public function it_can_get_recent_notifications()
    {
        Notification::factory()->count(15)->create([
            'user_id' => $this->user->id,
            'tenant_id' => $this->user->tenant_id,
        ]);

        $recent = $this->notificationService->getRecentNotifications($this->user, limit: 5);

        $this->assertCount(5, $recent);
    }
}
