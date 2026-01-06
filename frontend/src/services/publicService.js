import api from './api';

const publicService = {
  /**
   * Search available units with filters
   * @param {Object} filters - Search filters
   * @returns {Promise} API response
   */
  searchUnits: async (filters = {}) => {
    const params = new URLSearchParams();
    
    // Add filters to params
    Object.entries(filters).forEach(([key, value]) => {
      if (value !== null && value !== undefined && value !== '') {
        params.append(key, value);
      }
    });

    const response = await api.get(`/public/units?${params.toString()}`);
    return response.data;
  },

  /**
   * Get unit details by ID
   * @param {string} id - Unit ID
   * @returns {Promise} API response
   */
  getUnitDetails: async (id) => {
    const response = await api.get(`/public/units/${id}`);
    return response.data;
  },

  /**
   * Get available filter options
   * @returns {Promise} API response
   */
  getFilterOptions: async () => {
    const response = await api.get('/public/units/filter-options');
    return response.data;
  },

  /**
   * Submit a rental inquiry
   * @param {Object} data - Inquiry data
   * @returns {Promise} API response
   */
  submitInquiry: async (data) => {
    const response = await api.post('/public/inquiries', data);
    return response.data;
  },

  /**
   * Get inquiry status by ID
   * @param {string} id - Inquiry ID
   * @returns {Promise} API response
   */
  getInquiryStatus: async (id) => {
    const response = await api.get(`/public/inquiries/${id}`);
    return response.data;
  },

  /**
   * Create a reservation for a unit
   * @param {string} unitId - Unit ID
   * @param {Object} data - Reservation data
   * @returns {Promise} API response
   */
  createReservation: async (unitId, data) => {
    const response = await api.post(`/public/units/${unitId}/reserve`, data);
    return response.data;
  },

  /**
   * Get reservation details by ID
   * @param {string} id - Reservation ID
   * @returns {Promise} API response
   */
  getReservationStatus: async (id) => {
    const response = await api.get(`/public/reservations/${id}`);
    return response.data;
  },

  /**
   * Cancel a reservation
   * @param {string} id - Reservation ID
   * @returns {Promise} API response
   */
  cancelReservation: async (id) => {
    const response = await api.post(`/public/reservations/${id}/cancel`);
    return response.data;
  },
};

export default publicService;
