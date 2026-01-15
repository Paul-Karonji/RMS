import api from './api';

export const notificationService = {
    // Get paginated notifications
    async getNotifications(params = {}) {
        const response = await api.get('/notifications', { params });
        return response.data;
    },

    // Get unread count
    async getUnreadCount() {
        const response = await api.get('/notifications/unread-count');
        return response.data;
    },

    // Get recent notifications (for dropdown)
    async getRecent(limit = 10) {
        const response = await api.get('/notifications/recent', {
            params: { limit }
        });
        return response.data;
    },

    // Mark notification as read
    async markAsRead(notificationId) {
        const response = await api.patch(`/notifications/${notificationId}/read`);
        return response.data;
    },

    // Mark all as read
    async markAllAsRead() {
        const response = await api.patch('/notifications/read-all');
        return response.data;
    },

    // Delete notification
    async deleteNotification(notificationId) {
        const response = await api.delete(`/notifications/${notificationId}`);
        return response.data;
    },

    // Delete all read notifications
    async deleteAllRead() {
        const response = await api.delete('/notifications/read/all');
        return response.data;
    }
};
