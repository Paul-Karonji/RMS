import api from './api';

export const platformService = {
  // Authentication
  login: (credentials) => api.post('/platform/auth/login', credentials),
  logout: () => api.post('/platform/auth/logout'),
  me: () => api.get('/platform/auth/me'),

  // Dashboard
  getDashboard: (params) => api.get('/platform/dashboard', { params }),

  // Revenue
  getRevenue: (params) => api.get('/platform/revenue', { params }),

  // Companies (Tenants)
  getCompanies: (params) => api.get('/platform/tenants', { params }),
  getCompany: (id) => api.get(`/platform/tenants/${id}`),
  createCompany: (data) => api.post('/platform/tenants', data),
  updateCompany: (id, data) => api.put(`/platform/tenants/${id}`, data),
  deleteCompany: (id) => api.delete(`/platform/tenants/${id}`),
  suspendCompany: (id) => api.post(`/platform/tenants/${id}/suspend`),
  activateCompany: (id) => api.post(`/platform/tenants/${id}/activate`),
};
