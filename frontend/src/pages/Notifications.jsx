import React, { useState, useEffect } from 'react';
import { notificationService } from '../services/notificationService';
import { Filter, Trash2 } from 'lucide-react';
import NotificationItem from '../components/notifications/NotificationItem';
import './Notifications.css';

const Notifications = () => {
    const [notifications, setNotifications] = useState([]);
    const [loading, setLoading] = useState(true);
    const [filter, setFilter] = useState('all'); // all, unread, read
    const [currentPage, setCurrentPage] = useState(1);
    const [totalPages, setTotalPages] = useState(1);

    useEffect(() => {
        fetchNotifications();
    }, [filter, currentPage]);

    const fetchNotifications = async () => {
        try {
            setLoading(true);
            const params = {
                page: currentPage,
                per_page: 20
            };

            if (filter !== 'all') {
                params.status = filter;
            }

            const response = await notificationService.getNotifications(params);
            setNotifications(response.data);
            setTotalPages(response.meta.last_page);
        } catch (error) {
            console.error('Failed to fetch notifications:', error);
        } finally {
            setLoading(false);
        }
    };

    const handleMarkAllAsRead = async () => {
        try {
            await notificationService.markAllAsRead();
            fetchNotifications();
        } catch (error) {
            console.error('Failed to mark all as read:', error);
        }
    };

    const handleDeleteAllRead = async () => {
        if (!window.confirm('Are you sure you want to delete all read notifications?')) {
            return;
        }

        try {
            await notificationService.deleteAllRead();
            fetchNotifications();
        } catch (error) {
            console.error('Failed to delete read notifications:', error);
        }
    };

    const handleNotificationClick = async (notification) => {
        if (!notification.read_at) {
            try {
                await notificationService.markAsRead(notification.id);
                fetchNotifications();
            } catch (error) {
                console.error('Failed to mark as read:', error);
            }
        }
    };

    return (
        <div className="notifications-page">
            <div className="page-header">
                <h1>Notifications</h1>
                <div className="header-actions">
                    <button className="btn-secondary" onClick={handleMarkAllAsRead}>
                        Mark all as read
                    </button>
                    <button className="btn-secondary" onClick={handleDeleteAllRead}>
                        <Trash2 size={16} />
                        Delete read
                    </button>
                </div>
            </div>

            <div className="notifications-filters">
                <button
                    className={`filter-btn ${filter === 'all' ? 'active' : ''}`}
                    onClick={() => setFilter('all')}
                >
                    All
                </button>
                <button
                    className={`filter-btn ${filter === 'unread' ? 'active' : ''}`}
                    onClick={() => setFilter('unread')}
                >
                    Unread
                </button>
                <button
                    className={`filter-btn ${filter === 'read' ? 'active' : ''}`}
                    onClick={() => setFilter('read')}
                >
                    Read
                </button>
            </div>

            <div className="notifications-list">
                {loading ? (
                    <div className="loading-state">Loading notifications...</div>
                ) : notifications.length === 0 ? (
                    <div className="empty-state">
                        <p>No notifications found</p>
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

            {totalPages > 1 && (
                <div className="pagination">
                    <button
                        disabled={currentPage === 1}
                        onClick={() => setCurrentPage(currentPage - 1)}
                    >
                        Previous
                    </button>
                    <span>Page {currentPage} of {totalPages}</span>
                    <button
                        disabled={currentPage === totalPages}
                        onClick={() => setCurrentPage(currentPage + 1)}
                    >
                        Next
                    </button>
                </div>
            )}
        </div>
    );
};

export default Notifications;
