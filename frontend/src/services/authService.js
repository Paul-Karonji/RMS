import api from './api';

const authService = {
  /**
   * Login user with email and password
   */
  login: async (email, password) => {
    const response = await api.post('/auth/login', { email, password });
    return response.data;
  },

  /**
   * Register new property owner
   */
  register: async (data) => {
    const response = await api.post('/auth/register', {
      company_name: data.companyName,
      name: data.name,
      email: data.email,
      phone: data.phone,
      password: data.password,
      password_confirmation: data.passwordConfirmation,
    });
    return response.data;
  },

  /**
   * Request password reset link
   */
  forgotPassword: async (email) => {
    const response = await api.post('/auth/forgot-password', { email });
    return response.data;
  },

  /**
   * Reset password with token
   */
  resetPassword: async (data) => {
    const response = await api.post('/auth/reset-password', {
      token: data.token,
      email: data.email,
      password: data.password,
      password_confirmation: data.passwordConfirmation,
    });
    return response.data;
  },

  /**
   * Get current authenticated user
   */
  getCurrentUser: async () => {
    const response = await api.get('/auth/user');
    return response.data;
  },

  /**
   * Logout current device
   */
  logout: async () => {
    const response = await api.post('/auth/logout');
    return response.data;
  },

  /**
   * Logout all devices
   */
  logoutAll: async () => {
    const response = await api.post('/auth/logout-all');
    return response.data;
  },
};

export default authService;
