import api from './api';

/**
 * Tenant & Lease Management Service
 * Handles all API calls for tenant, inquiry, and lease operations
 */
const tenantService = {
  // ==========================================================================
  // TENANT ENDPOINTS
  // ==========================================================================

  /**
   * Get all tenants with optional filters
   * @param {Object} filters - { status, search, page, per_page }
   */
  getTenants: async (filters = {}) => {
    try {
      const params = new URLSearchParams();
      if (filters.status) params.append('status', filters.status);
      if (filters.search) params.append('search', filters.search);
      if (filters.page) params.append('page', filters.page);
      if (filters.per_page) params.append('per_page', filters.per_page);

      const response = await api.get(`/tenants?${params.toString()}`);
      return response.data;
    } catch (error) {
      throw error.response?.data || { message: 'Failed to fetch tenants' };
    }
  },

  /**
   * Get a single tenant by ID
   * @param {string} id - Tenant UUID
   */
  getTenant: async (id) => {
    try {
      const response = await api.get(`/tenants/${id}`);
      return response.data;
    } catch (error) {
      throw error.response?.data || { message: 'Failed to fetch tenant' };
    }
  },

  /**
   * Create a new tenant
   * @param {Object} data - Tenant data
   */
  createTenant: async (data) => {
    try {
      const response = await api.post('/tenants', data);
      return response.data;
    } catch (error) {
      throw error.response?.data || { message: 'Failed to create tenant' };
    }
  },

  /**
   * Update an existing tenant
   * @param {string} id - Tenant UUID
   * @param {Object} data - Updated tenant data
   */
  updateTenant: async (id, data) => {
    try {
      const response = await api.put(`/tenants/${id}`, data);
      return response.data;
    } catch (error) {
      throw error.response?.data || { message: 'Failed to update tenant' };
    }
  },

  /**
   * Delete a tenant
   * @param {string} id - Tenant UUID
   */
  deleteTenant: async (id) => {
    try {
      const response = await api.delete(`/tenants/${id}`);
      return response.data;
    } catch (error) {
      throw error.response?.data || { message: 'Failed to delete tenant' };
    }
  },

  // ==========================================================================
  // INQUIRY ENDPOINTS
  // ==========================================================================

  /**
   * Get all inquiries with optional filters
   * @param {Object} filters - { status, unit_id, search, page, per_page }
   */
  getInquiries: async (filters = {}) => {
    try {
      const params = new URLSearchParams();
      if (filters.status) params.append('status', filters.status);
      if (filters.unit_id) params.append('unit_id', filters.unit_id);
      if (filters.search) params.append('search', filters.search);
      if (filters.page) params.append('page', filters.page);
      if (filters.per_page) params.append('per_page', filters.per_page);

      const response = await api.get(`/inquiries?${params.toString()}`);
      return response.data;
    } catch (error) {
      throw error.response?.data || { message: 'Failed to fetch inquiries' };
    }
  },

  /**
   * Approve an inquiry (creates tenant account)
   * @param {string} id - Inquiry UUID
   */
  approveInquiry: async (id) => {
    try {
      const response = await api.patch(`/inquiries/${id}/approve`);
      return response.data;
    } catch (error) {
      throw error.response?.data || { message: 'Failed to approve inquiry' };
    }
  },

  /**
   * Reject an inquiry with reason
   * @param {string} id - Inquiry UUID
   * @param {string} reason - Rejection reason
   */
  rejectInquiry: async (id, reason) => {
    try {
      const response = await api.patch(`/inquiries/${id}/reject`, {
        rejection_reason: reason,
      });
      return response.data;
    } catch (error) {
      throw error.response?.data || { message: 'Failed to reject inquiry' };
    }
  },

  // ==========================================================================
  // LEASE ENDPOINTS
  // ==========================================================================

  /**
   * Get all leases with optional filters
   * @param {Object} filters - { status, property_id, tenant_id, search, page, per_page }
   */
  getLeases: async (filters = {}) => {
    try {
      const params = new URLSearchParams();
      if (filters.status) params.append('status', filters.status);
      if (filters.property_id) params.append('property_id', filters.property_id);
      if (filters.tenant_id) params.append('tenant_id', filters.tenant_id);
      if (filters.search) params.append('search', filters.search);
      if (filters.page) params.append('page', filters.page);
      if (filters.per_page) params.append('per_page', filters.per_page);

      const response = await api.get(`/leases?${params.toString()}`);
      return response.data;
    } catch (error) {
      throw error.response?.data || { message: 'Failed to fetch leases' };
    }
  },

  /**
   * Get a single lease by ID
   * @param {string} id - Lease UUID
   */
  getLease: async (id) => {
    try {
      const response = await api.get(`/leases/${id}`);
      return response.data;
    } catch (error) {
      throw error.response?.data || { message: 'Failed to fetch lease' };
    }
  },

  /**
   * Create a new lease
   * @param {Object} data - Lease data
   */
  createLease: async (data) => {
    try {
      const response = await api.post('/leases', data);
      return response.data;
    } catch (error) {
      throw error.response?.data || { message: 'Failed to create lease' };
    }
  },

  /**
   * Update an existing lease
   * @param {string} id - Lease UUID
   * @param {Object} data - Updated lease data
   */
  updateLease: async (id, data) => {
    try {
      const response = await api.put(`/leases/${id}`, data);
      return response.data;
    } catch (error) {
      throw error.response?.data || { message: 'Failed to update lease' };
    }
  },

  /**
   * Delete a lease
   * @param {string} id - Lease UUID
   */
  deleteLease: async (id) => {
    try {
      const response = await api.delete(`/leases/${id}`);
      return response.data;
    } catch (error) {
      throw error.response?.data || { message: 'Failed to delete lease' };
    }
  },

  /**
   * Terminate an active lease
   * @param {string} id - Lease UUID
   * @param {Object} data - { termination_reason, termination_date }
   */
  terminateLease: async (id, data) => {
    try {
      const response = await api.patch(`/leases/${id}/terminate`, data);
      return response.data;
    } catch (error) {
      throw error.response?.data || { message: 'Failed to terminate lease' };
    }
  },

  /**
   * Renew a lease
   * @param {string} id - Lease UUID
   * @param {Object} data - { new_end_date, new_rent_amount }
   */
  renewLease: async (id, data) => {
    try {
      const response = await api.post(`/leases/${id}/renew`, data);
      return response.data;
    } catch (error) {
      throw error.response?.data || { message: 'Failed to renew lease' };
    }
  },

  // ==========================================================================
  // UTILITY FUNCTIONS
  // ==========================================================================

  /**
   * Calculate pro-rated rent based on move-in date
   * Business rule: Day 1-15 = full rent, Day 16-31 = half rent
   * @param {string} startDate - Lease start date (YYYY-MM-DD)
   * @param {number} monthlyRent - Monthly rent amount
   * @returns {Object} { amount, isProrated, type, note }
   */
  calculateProRatedRent: (startDate, monthlyRent) => {
    const date = new Date(startDate);
    const dayOfMonth = date.getDate();
    const daysInMonth = new Date(date.getFullYear(), date.getMonth() + 1, 0).getDate();
    
    if (dayOfMonth <= 15) {
      return {
        amount: monthlyRent,
        isProrated: false,
        type: 'Full Month',
        note: `Full month rent - moved in on day ${dayOfMonth}`,
        daysRemaining: daysInMonth - dayOfMonth + 1,
      };
    } else {
      return {
        amount: monthlyRent / 2,
        isProrated: true,
        type: 'Half Month (Prorated)',
        note: `Half month rent - moved in on day ${dayOfMonth}`,
        daysRemaining: daysInMonth - dayOfMonth + 1,
      };
    }
  },

  /**
   * Calculate first payment breakdown
   * @param {string} startDate - Lease start date
   * @param {number} monthlyRent - Monthly rent amount
   * @param {number} depositAmount - Deposit amount
   * @returns {Object} First payment breakdown
   */
  calculateFirstPayment: (startDate, monthlyRent, depositAmount) => {
    const rentCalc = tenantService.calculateProRatedRent(startDate, monthlyRent);
    return {
      rent: rentCalc,
      deposit: depositAmount,
      total: rentCalc.amount + depositAmount,
    };
  },

  /**
   * Format currency for display
   * @param {number} amount - Amount to format
   * @returns {string} Formatted currency string
   */
  formatCurrency: (amount) => {
    return new Intl.NumberFormat('en-KE', {
      style: 'currency',
      currency: 'KES',
      minimumFractionDigits: 0,
      maximumFractionDigits: 0,
    }).format(amount);
  },
};

export default tenantService;
