<?php

namespace App\Enums;

enum NotificationType: string
{
    // Payment Notifications
    case PAYMENT_RECEIVED = 'payment_received';
    case PAYMENT_DUE = 'payment_due';
    case PAYMENT_OVERDUE = 'payment_overdue';
    case PAYMENT_FAILED = 'payment_failed';
    
    // Lease Notifications
    case LEASE_CREATED = 'lease_created';
    case LEASE_EXPIRING = 'lease_expiring';
    case LEASE_TERMINATED = 'lease_terminated';
    case LEASE_RENEWED = 'lease_renewed';
    
    // Property Notifications
    case PROPERTY_SUBMITTED = 'property_submitted';
    case PROPERTY_APPROVED = 'property_approved';
    case PROPERTY_REJECTED = 'property_rejected';
    
    // Maintenance Notifications
    case MAINTENANCE_REQUESTED = 'maintenance_requested';
    case MAINTENANCE_ASSIGNED = 'maintenance_assigned';
    case MAINTENANCE_COMPLETED = 'maintenance_completed';
    
    // Cashout Notifications
    case CASHOUT_REQUESTED = 'cashout_requested';
    case CASHOUT_APPROVED = 'cashout_approved';
    case CASHOUT_REJECTED = 'cashout_rejected';
    case CASHOUT_PROCESSED = 'cashout_processed';
    
    // Owner Payment Notifications
    case OWNER_PAYMENT_MARKED = 'owner_payment_marked';
    
    // Change Request Notifications
    case CHANGE_REQUEST_SUBMITTED = 'change_request_submitted';
    case CHANGE_REQUEST_APPROVED = 'change_request_approved';
    case CHANGE_REQUEST_REJECTED = 'change_request_rejected';
    
    // Inquiry Notifications
    case INQUIRY_RECEIVED = 'inquiry_received';
    case INQUIRY_APPROVED = 'inquiry_approved';
    case INQUIRY_REJECTED = 'inquiry_rejected';

    /**
     * Get a human-readable label for the notification type.
     */
    public function label(): string
    {
        return match($this) {
            self::PAYMENT_RECEIVED => 'Payment Received',
            self::PAYMENT_DUE => 'Payment Due',
            self::PAYMENT_OVERDUE => 'Payment Overdue',
            self::PAYMENT_FAILED => 'Payment Failed',
            self::LEASE_CREATED => 'Lease Created',
            self::LEASE_EXPIRING => 'Lease Expiring Soon',
            self::LEASE_TERMINATED => 'Lease Terminated',
            self::LEASE_RENEWED => 'Lease Renewed',
            self::PROPERTY_SUBMITTED => 'Property Submitted',
            self::PROPERTY_APPROVED => 'Property Approved',
            self::PROPERTY_REJECTED => 'Property Rejected',
            self::MAINTENANCE_REQUESTED => 'Maintenance Requested',
            self::MAINTENANCE_ASSIGNED => 'Maintenance Assigned',
            self::MAINTENANCE_COMPLETED => 'Maintenance Completed',
            self::CASHOUT_REQUESTED => 'Cashout Requested',
            self::CASHOUT_APPROVED => 'Cashout Approved',
            self::CASHOUT_REJECTED => 'Cashout Rejected',
            self::CASHOUT_PROCESSED => 'Cashout Processed',
            self::OWNER_PAYMENT_MARKED => 'Payment Marked',
            self::CHANGE_REQUEST_SUBMITTED => 'Change Request Submitted',
            self::CHANGE_REQUEST_APPROVED => 'Change Request Approved',
            self::CHANGE_REQUEST_REJECTED => 'Change Request Rejected',
            self::INQUIRY_RECEIVED => 'Inquiry Received',
            self::INQUIRY_APPROVED => 'Inquiry Approved',
            self::INQUIRY_REJECTED => 'Inquiry Rejected',
        };
    }

    /**
     * Get the icon for the notification type.
     */
    public function icon(): string
    {
        return match($this) {
            self::PAYMENT_RECEIVED, self::PAYMENT_DUE => 'currency-dollar',
            self::PAYMENT_OVERDUE, self::PAYMENT_FAILED => 'exclamation-circle',
            self::LEASE_CREATED, self::LEASE_RENEWED => 'document-text',
            self::LEASE_EXPIRING, self::LEASE_TERMINATED => 'clock',
            self::PROPERTY_SUBMITTED, self::PROPERTY_APPROVED => 'home',
            self::PROPERTY_REJECTED => 'x-circle',
            self::MAINTENANCE_REQUESTED, self::MAINTENANCE_ASSIGNED, self::MAINTENANCE_COMPLETED => 'wrench',
            self::CASHOUT_REQUESTED, self::CASHOUT_APPROVED, self::CASHOUT_PROCESSED => 'cash',
            self::CASHOUT_REJECTED => 'x-circle',
            self::OWNER_PAYMENT_MARKED => 'check-circle',
            self::CHANGE_REQUEST_SUBMITTED, self::CHANGE_REQUEST_APPROVED, self::CHANGE_REQUEST_REJECTED => 'pencil',
            self::INQUIRY_RECEIVED, self::INQUIRY_APPROVED, self::INQUIRY_REJECTED => 'mail',
        };
    }

    /**
     * Get the color for the notification type.
     */
    public function color(): string
    {
        return match($this) {
            self::PAYMENT_RECEIVED, self::PROPERTY_APPROVED, self::MAINTENANCE_COMPLETED, 
            self::CASHOUT_APPROVED, self::CASHOUT_PROCESSED, self::OWNER_PAYMENT_MARKED,
            self::CHANGE_REQUEST_APPROVED, self::INQUIRY_APPROVED => 'success',
            
            self::PAYMENT_OVERDUE, self::PAYMENT_FAILED, self::PROPERTY_REJECTED,
            self::CASHOUT_REJECTED, self::CHANGE_REQUEST_REJECTED, self::INQUIRY_REJECTED => 'error',
            
            self::PAYMENT_DUE, self::LEASE_EXPIRING => 'warning',
            
            default => 'info',
        };
    }
}
