import api from './api';

export const changeRequestService = {
    // Get paginated change requests
    async getChangeRequests(params = {}) {
        const response = await api.get('/change-requests', { params });
        return response.data;
    },

    // Get single change request
    async getChangeRequest(id) {
        const response = await api.get(`/change-requests/${id}`);
        return response.data;
    },

    // Create change request
    async createChangeRequest(data) {
        const response = await api.post('/change-requests', data);
        return response.data;
    },

    // Approve change request
    async approveChangeRequest(id, notes) {
        const response = await api.patch(`/change-requests/${id}/approve`, { notes });
        return response.data;
    },

    // Reject change request
    async rejectChangeRequest(id, reason) {
        const response = await api.patch(`/change-requests/${id}/reject`, { reason });
        return response.data;
    }
};
