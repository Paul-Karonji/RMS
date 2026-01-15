import React from 'react';
import { formatDistanceToNow } from 'date-fns';
import './NotificationItem.css';

const NotificationItem = ({ notification, onClick }) => {
    const getNotificationIcon = (type) => {
        // Map notification types to icons
        const iconMap = {
            payment_received: '💰',
            payment_due: '⏰',
            payment_overdue: '⚠️',
            property_approved: '🏠',
            property_rejected: '❌',
            lease_created: '📄',
            maintenance_requested: '🔧',
            cashout_approved: '✅',
            change_request_submitted: '📝',
            default: '🔔'
        };
        return iconMap[type] || iconMap.default;
    };

    const getNotificationColor = (type) => {
        if (type.includes('approved') || type.includes('received')) return 'success';
        if (type.includes('rejected') || type.includes('overdue')) return 'error';
        if (type.includes('due') || type.includes('expiring')) return 'warning';
        return 'info';
    };

    return (
        <div
            className={`notification-item ${!notification.read_at ? 'unread' : ''}`}
            onClick={onClick}
        >
            <div className={`notification-icon ${getNotificationColor(notification.type)}`}>
                {getNotificationIcon(notification.type)}
            </div>

            <div className="notification-content">
                <h4 className="notification-title">{notification.title}</h4>
                <p className="notification-message">{notification.message}</p>
                <span className="notification-time">
                    {formatDistanceToNow(new Date(notification.created_at), { addSuffix: true })}
                </span>
            </div>

            {!notification.read_at && (
                <div className="notification-unread-dot"></div>
            )}
        </div>
    );
};

export default NotificationItem;
