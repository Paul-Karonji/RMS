<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Enums\NotificationType;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class NotificationService
{
    /**
     * Create a new notification for a user.
     */
    public function create(
        User $user,
        string|NotificationType $type,
        string $title,
        string $message,
        ?array $data = null
    ): Notification {
        $typeValue = $type instanceof NotificationType ? $type->value : $type;

        return Notification::create([
            'tenant_id' => $user->tenant_id,
            'user_id' => $user->id,
            'type' => $typeValue,
            'title' => $title,
            'message' => $message,
            'status' => 'unread',
            'data' => $data,
        ]);
    }

    /**
     * Create notifications for multiple users.
     */
    public function createForUsers(
        Collection|array $users,
        string|NotificationType $type,
        string $title,
        string $message,
        ?array $data = null
    ): Collection {
        $notifications = collect();

        foreach ($users as $user) {
            $notifications->push(
                $this->create($user, $type, $title, $message, $data)
            );
        }

        return $notifications;
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead(string $notificationId, User $user): bool
    {
        $notification = Notification::where('id', $notificationId)
            ->where('user_id', $user->id)
            ->first();

        if (!$notification) {
            return false;
        }

        return $notification->markAsRead();
    }

    /**
     * Mark all notifications as read for a user.
     */
    public function markAllAsRead(User $user): int
    {
        return Notification::where('user_id', $user->id)
            ->whereNull('read_at')
            ->update([
                'read_at' => now(),
                'status' => 'read',
            ]);
    }

    /**
     * Get unread notification count for a user.
     */
    public function getUnreadCount(User $user): int
    {
        return Notification::where('user_id', $user->id)
            ->unread()
            ->count();
    }

    /**
     * Get paginated notifications for a user.
     */
    public function getUserNotifications(
        User $user,
        ?string $status = null,
        int $perPage = 20
    ): LengthAwarePaginator {
        $query = Notification::where('user_id', $user->id)
            ->latest('created_at');

        if ($status === 'unread') {
            $query->unread();
        } elseif ($status === 'read') {
            $query->read();
        }

        return $query->paginate($perPage);
    }

    /**
     * Delete a notification.
     */
    public function delete(string $notificationId, User $user): bool
    {
        return Notification::where('id', $notificationId)
            ->where('user_id', $user->id)
            ->delete();
    }

    /**
     * Delete all read notifications for a user.
     */
    public function deleteAllRead(User $user): int
    {
        return Notification::where('user_id', $user->id)
            ->read()
            ->delete();
    }

    /**
     * Get recent notifications for a user (for dropdown).
     */
    public function getRecentNotifications(User $user, int $limit = 10): Collection
    {
        return Notification::where('user_id', $user->id)
            ->latest('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Send notification about payment received.
     */
    public function notifyPaymentReceived(User $user, array $paymentData): Notification
    {
        return $this->create(
            user: $user,
            type: NotificationType::PAYMENT_RECEIVED,
            title: 'Payment Received',
            message: "Payment of KES " . number_format($paymentData['amount'], 2) . " has been received.",
            data: $paymentData
        );
    }

    /**
     * Send notification about payment due.
     */
    public function notifyPaymentDue(User $user, array $paymentData): Notification
    {
        return $this->create(
            user: $user,
            type: NotificationType::PAYMENT_DUE,
            title: 'Payment Due',
            message: "Your rent payment of KES " . number_format($paymentData['amount'], 2) . " is due on " . $paymentData['due_date'] . ".",
            data: $paymentData
        );
    }

    /**
     * Send notification about property approval.
     */
    public function notifyPropertyApproved(User $user, array $propertyData): Notification
    {
        return $this->create(
            user: $user,
            type: NotificationType::PROPERTY_APPROVED,
            title: 'Property Approved',
            message: "Your property '{$propertyData['name']}' has been approved.",
            data: $propertyData
        );
    }

    /**
     * Send notification about property rejection.
     */
    public function notifyPropertyRejected(User $user, array $propertyData): Notification
    {
        return $this->create(
            user: $user,
            type: NotificationType::PROPERTY_REJECTED,
            title: 'Property Rejected',
            message: "Your property '{$propertyData['name']}' was rejected. Reason: {$propertyData['reason']}",
            data: $propertyData
        );
    }

    /**
     * Send notification about lease creation.
     */
    public function notifyLeaseCreated(User $user, array $leaseData): Notification
    {
        return $this->create(
            user: $user,
            type: NotificationType::LEASE_CREATED,
            title: 'Lease Created',
            message: "A new lease has been created for unit {$leaseData['unit_number']}.",
            data: $leaseData
        );
    }

    /**
     * Send notification about maintenance request.
     */
    public function notifyMaintenanceAssigned(User $user, array $maintenanceData): Notification
    {
        return $this->create(
            user: $user,
            type: NotificationType::MAINTENANCE_ASSIGNED,
            title: 'Maintenance Assigned',
            message: "A maintenance request has been assigned to you for unit {$maintenanceData['unit_number']}.",
            data: $maintenanceData
        );
    }

    /**
     * Send notification about cashout approval.
     */
    public function notifyCashoutApproved(User $user, array $cashoutData): Notification
    {
        return $this->create(
            user: $user,
            type: NotificationType::CASHOUT_APPROVED,
            title: 'Cashout Approved',
            message: "Your cashout request of KES " . number_format($cashoutData['amount'], 2) . " has been approved.",
            data: $cashoutData
        );
    }

    /**
     * Send notification about change request.
     */
    public function notifyChangeRequestSubmitted(User $user, array $requestData): Notification
    {
        return $this->create(
            user: $user,
            type: NotificationType::CHANGE_REQUEST_SUBMITTED,
            title: 'Change Request Submitted',
            message: "A change request has been submitted for {$requestData['property_name']}.",
            data: $requestData
        );
    }
}
