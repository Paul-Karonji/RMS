import api from './api';

const propertyService = {
  // ==========================================================================
  // PROPERTY ENDPOINTS
  // ==========================================================================

  /**
   * Get all properties with optional filters
   * @param {Object} filters - { status, property_type, search, page, per_page }
   */
  getProperties: async (filters = {}) => {
    try {
      const params = new URLSearchParams();
      if (filters.status) params.append('status', filters.status);
      if (filters.property_type) params.append('property_type', filters.property_type);
      if (filters.search) params.append('search', filters.search);
      if (filters.page) params.append('page', filters.page);
      if (filters.per_page) params.append('per_page', filters.per_page);

      const response = await api.get(`/properties?${params.toString()}`);
      return response.data;
    } catch (error) {
      throw error.response?.data || { message: 'Failed to fetch properties' };
    }
  },

  /**
   * Get a single property by ID
   * @param {string} id - Property UUID
   */
  getProperty: async (id) => {
    try {
      const response = await api.get(`/properties/${id}`);
      return response.data;
    } catch (error) {
      throw error.response?.data || { message: 'Failed to fetch property' };
    }
  },

  /**
   * Create a new property
   * @param {Object} data - Property data
   */
  createProperty: async (data) => {
    try {
      const response = await api.post('/properties', data);
      return response.data;
    } catch (error) {
      throw error.response?.data || { message: 'Failed to create property' };
    }
  },

  /**
   * Update an existing property
   * @param {string} id - Property UUID
   * @param {Object} data - Updated property data
   */
  updateProperty: async (id, data) => {
    try {
      const response = await api.put(`/properties/${id}`, data);
      return response.data;
    } catch (error) {
      throw error.response?.data || { message: 'Failed to update property' };
    }
  },

  /**
   * Delete a property
   * @param {string} id - Property UUID
   */
  deleteProperty: async (id) => {
    try {
      const response = await api.delete(`/properties/${id}`);
      return response.data;
    } catch (error) {
      throw error.response?.data || { message: 'Failed to delete property' };
    }
  },

  /**
   * Resubmit a rejected property for approval
   * @param {string} id - Property UUID
   */
  resubmitProperty: async (id) => {
    try {
      const response = await api.post(`/properties/${id}/resubmit`);
      return response.data;
    } catch (error) {
      throw error.response?.data || { message: 'Failed to resubmit property' };
    }
  },

  /**
   * Approve a property (Admin only)
   * @param {string} id - Property UUID
   */
  approveProperty: async (id) => {
    try {
      const response = await api.patch(`/properties/${id}/approve`);
      return response.data;
    } catch (error) {
      throw error.response?.data || { message: 'Failed to approve property' };
    }
  },

  /**
   * Reject a property (Admin only)
   * @param {string} id - Property UUID
   * @param {string} reason - Rejection reason
   */
  rejectProperty: async (id, reason) => {
    try {
      const response = await api.patch(`/properties/${id}/reject`, {
        rejection_reason: reason,
      });
      return response.data;
    } catch (error) {
      throw error.response?.data || { message: 'Failed to reject property' };
    }
  },

  /**
   * Assign a manager to a property (Admin only)
   * @param {string} id - Property UUID
   * @param {string} managerId - Manager user UUID
   */
  assignManager: async (id, managerId) => {
    try {
      const response = await api.post(`/properties/${id}/assign-manager`, {
        manager_id: managerId,
      });
      return response.data;
    } catch (error) {
      throw error.response?.data || { message: 'Failed to assign manager' };
    }
  },

  // ==========================================================================
  // UNIT ENDPOINTS
  // ==========================================================================

  /**
   * Get all units with optional filters
   * @param {Object} filters - { property_id, status, search, page, per_page }
   */
  getUnits: async (filters = {}) => {
    try {
      const params = new URLSearchParams();
      if (filters.property_id) params.append('property_id', filters.property_id);
      if (filters.status) params.append('status', filters.status);
      if (filters.search) params.append('search', filters.search);
      if (filters.page) params.append('page', filters.page);
      if (filters.per_page) params.append('per_page', filters.per_page);

      const response = await api.get(`/units?${params.toString()}`);
      return response.data;
    } catch (error) {
      throw error.response?.data || { message: 'Failed to fetch units' };
    }
  },

  /**
   * Get a single unit by ID
   * @param {string} id - Unit UUID
   */
  getUnit: async (id) => {
    try {
      const response = await api.get(`/units/${id}`);
      return response.data;
    } catch (error) {
      throw error.response?.data || { message: 'Failed to fetch unit' };
    }
  },

  /**
   * Create a new unit for a property
   * @param {string} propertyId - Property UUID
   * @param {Object} data - Unit data
   */
  createUnit: async (propertyId, data) => {
    try {
      const response = await api.post(`/properties/${propertyId}/units`, data);
      return response.data;
    } catch (error) {
      throw error.response?.data || { message: 'Failed to create unit' };
    }
  },

  /**
   * Update an existing unit
   * @param {string} id - Unit UUID
   * @param {Object} data - Updated unit data
   */
  updateUnit: async (id, data) => {
    try {
      const response = await api.put(`/units/${id}`, data);
      return response.data;
    } catch (error) {
      throw error.response?.data || { message: 'Failed to update unit' };
    }
  },

  /**
   * Delete a unit
   * @param {string} id - Unit UUID
   */
  deleteUnit: async (id) => {
    try {
      const response = await api.delete(`/units/${id}`);
      return response.data;
    } catch (error) {
      throw error.response?.data || { message: 'Failed to delete unit' };
    }
  },
};

export default propertyService;
