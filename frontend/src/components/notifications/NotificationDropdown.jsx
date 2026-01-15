import React, { useState, useEffect } from 'react';
import { X, CheckCheck } from 'lucide-react';
import { notificationService } from '../../services/notificationService';
import NotificationItem from './NotificationItem';
import './NotificationDropdown.css';

const NotificationDropdown = ({ onClose, onNotificationRead }) => {
    const [notifications, setNotifications] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        fetchNotifications();
    }, []);

    const fetchNotifications = async () => {
        try {
            setLoading(true);
            const response = await notificationService.getRecent(10);
            setNotifications(response.data);
        } catch (error) {
            console.error('Failed to fetch notifications:', error);
        } finally {
            setLoading(false);
        }
    };

    const handleMarkAllAsRead = async () => {
        try {
            await notificationService.markAllAsRead();
            setNotifications(notifications.map(n => ({ ...n, read_at: new Date().toISOString() })));
            onNotificationRead();
        } catch (error) {
            console.error('Failed to mark all as read:', error);
        }
    };

    const handleNotificationClick = async (notification) => {
        if (!notification.read_at) {
            try {
                await notificationService.markAsRead(notification.id);
                setNotifications(notifications.map(n =>
                    n.id === notification.id ? { ...n, read_at: new Date().toISOString() } : n
                ));
                onNotificationRead();
            } catch (error) {
                console.error('Failed to mark as read:', error);
            }
        }
    };

    return (
        <div className="notification-dropdown">
            <div className="notification-dropdown-header">
                <h3>Notifications</h3>
                <div className="notification-dropdown-actions">
                    {notifications.some(n => !n.read_at) && (
                        <button
                            className="mark-all-read-btn"
                            onClick={handleMarkAllAsRead}
                            title="Mark all as read"
                        >
                            <CheckCheck size={18} />
                        </button>
                    )}
                    <button
                        className="close-dropdown-btn"
                        onClick={onClose}
                        aria-label="Close"
                    >
                        <X size={18} />
                    </button>
                </div>
            </div>

            <div className="notification-dropdown-body">
                {loading ? (
                    <div className="notification-loading">Loading...</div>
                ) : notifications.length === 0 ? (
                    <div className="notification-empty">
                        <p>No notifications</p>
                    </div>
                ) : (
                    notifications.map(notification => (
                        <NotificationItem
                            key={notification.id}
                            notification={notification}
                            onClick={() => handleNotificationClick(notification)}
                        />
                    ))
                )}
            </div>

            <div className="notification-dropdown-footer">
                <a href="/notifications" className="view-all-link">
                    View all notifications
                </a>
            </div>
        </div>
    );
};

export default NotificationDropdown;
