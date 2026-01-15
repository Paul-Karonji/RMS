<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(
        private NotificationService $notificationService
    ) {}

    /**
     * Get paginated notifications for authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $status = $request->query('status'); // unread, read, or null for all
        $perPage = min($request->query('per_page', 20), 100);

        $notifications = $this->notificationService->getUserNotifications(
            user: $request->user(),
            status: $status,
            perPage: $perPage
        );

        return response()->json([
            'success' => true,
            'data' => $notifications->items(),
            'meta' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
            ],
        ]);
    }

    /**
     * Get unread notification count.
     */
    public function unreadCount(Request $request): JsonResponse
    {
        $count = $this->notificationService->getUnreadCount($request->user());

        return response()->json([
            'success' => true,
            'data' => [
                'unread_count' => $count,
            ],
        ]);
    }

    /**
     * Get recent notifications (for dropdown).
     */
    public function recent(Request $request): JsonResponse
    {
        $limit = min($request->query('limit', 10), 50);
        
        $notifications = $this->notificationService->getRecentNotifications(
            user: $request->user(),
            limit: $limit
        );

        return response()->json([
            'success' => true,
            'data' => $notifications,
        ]);
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead(Request $request, string $id): JsonResponse
    {
        $success = $this->notificationService->markAsRead($id, $request->user());

        if (!$success) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read',
        ]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $count = $this->notificationService->markAllAsRead($request->user());

        return response()->json([
            'success' => true,
            'message' => "{$count} notifications marked as read",
            'data' => [
                'count' => $count,
            ],
        ]);
    }

    /**
     * Delete a notification.
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $success = $this->notificationService->delete($id, $request->user());

        if (!$success) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Notification deleted',
        ]);
    }

    /**
     * Delete all read notifications.
     */
    public function deleteAllRead(Request $request): JsonResponse
    {
        $count = $this->notificationService->deleteAllRead($request->user());

        return response()->json([
            'success' => true,
            'message' => "{$count} notifications deleted",
            'data' => [
                'count' => $count,
            ],
        ]);
    }
}
