import { api } from 'boot/axios';
import { Notify } from 'quasar';

// API Base URL
const API_BASE_URL = 'http://127.0.0.1:8000/api';

// Configure axios instance
api.defaults.baseURL = API_BASE_URL;

// Request interceptor to add JWT token
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('jwt_token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Response interceptor to handle errors
api.interceptors.response.use(
  (response) => {
    return response;
  },
  (error) => {
    if (error.response) {
      // Token expired or invalid
      if (error.response.status === 401) {
        localStorage.removeItem('jwt_token');
        localStorage.removeItem('user');
        window.location.href = '/login';
        
        Notify.create({
          type: 'negative',
          message: 'Session expired. Please login again.',
          position: 'top',
        });
      }
      
      // Forbidden
      if (error.response.status === 403) {
        Notify.create({
          type: 'negative',
          message: 'You do not have permission to perform this action.',
          position: 'top',
        });
      }
      
      // Server error
      if (error.response.status >= 500) {
        Notify.create({
          type: 'negative',
          message: 'Server error. Please try again later.',
          position: 'top',
        });
      }
    }
    
    return Promise.reject(error);
  }
);

// Auth API
export const authAPI = {
  login: (credentials) => api.post('/auth/login', credentials),
  register: (userData) => api.post('/auth/register', userData),
  logout: () => api.post('/auth/logout'),
  me: () => api.get('/auth/me'),
  refresh: () => api.post('/auth/refresh'),
};

// Users API
export const usersAPI = {
  getAll: () => api.get('/users'),
  getById: (id) => api.get(`/users/${id}`),
  create: (data) => api.post('/users', data),
  update: (id, data) => api.put(`/users/${id}`, data),
  delete: (id) => api.delete(`/users/${id}`),
  getContributions: (id) => api.get(`/users/${id}/contributions`),
  getMinistries: (id) => api.get(`/users/${id}/ministries`),
  assignRole: (id, roleId) => api.post(`/users/${id}/roles`, { role_id: roleId }),
  removeRole: (id, roleId) => api.delete(`/users/${id}/roles/${roleId}`),
};

// Announcements API
export const announcementsAPI = {
  getAll: () => api.get('/announcements'),
  getPublished: () => api.get('/announcements/published'),
  getById: (id) => api.get(`/announcements/${id}`),
  create: (data) => api.post('/announcements', data),
  update: (id, data) => api.put(`/announcements/${id}`, data),
  delete: (id) => api.delete(`/announcements/${id}`),
  publish: (id) => api.post(`/announcements/${id}/publish`),
};

// Events API
export const eventsAPI = {
  getAll: () => api.get('/events'),
  getUpcoming: () => api.get('/events/upcoming'),
  getPublished: () => api.get('/events/published'),
  getById: (id) => api.get(`/events/${id}`),
  create: (data) => api.post('/events', data),
  update: (id, data) => api.put(`/events/${id}`, data),
  delete: (id) => api.delete(`/events/${id}`),
  publish: (id) => api.post(`/events/${id}/publish`),
};

// Messages API
export const messagesAPI = {
  getAll: () => api.get('/messages'),
  getInbox: () => api.get('/messages/inbox'),
  getSent: () => api.get('/messages/sent'),
  getById: (id) => api.get(`/messages/${id}`),
  send: (data) => api.post('/messages', data),
  broadcast: (data) => api.post('/messages/broadcast', data),
  markAsRead: (id) => api.post(`/messages/${id}/read`),
  delete: (id) => api.delete(`/messages/${id}`),
};

// Contributions API
export const contributionsAPI = {
  getAll: () => api.get('/contributions'),
  getById: (id) => api.get(`/contributions/${id}`),
  create: (data) => api.post('/contributions', data),
  update: (id, data) => api.put(`/contributions/${id}`, data),
  delete: (id) => api.delete(`/contributions/${id}`),
  getSummary: () => api.get('/contributions/reports/summary'),
  getByType: () => api.get('/contributions/reports/by-type'),
  getByMinistry: () => api.get('/contributions/reports/by-ministry'),
  getByDateRange: (startDate, endDate) => 
    api.get(`/contributions/reports/by-date-range?start_date=${startDate}&end_date=${endDate}`),
};

// Ministries API
export const ministriesAPI = {
  getAll: () => api.get('/ministries'),
  getById: (id) => api.get(`/ministries/${id}`),
  create: (data) => api.post('/ministries', data),
  update: (id, data) => api.put(`/ministries/${id}`, data),
  delete: (id) => api.delete(`/ministries/${id}`),
  getMembers: (id) => api.get(`/ministries/${id}/members`),
  addMember: (id, data) => api.post(`/ministries/${id}/members`, data),
  removeMember: (id, userId) => api.delete(`/ministries/${id}/members/${userId}`),
  getContributions: (id) => api.get(`/ministries/${id}/contributions`),
};

// Dashboard API
export const dashboardAPI = {
  getUserStats: () => api.get('/dashboard/user'),
  getAdminStats: () => api.get('/dashboard/administrator'),
  getPastorStats: () => api.get('/dashboard/pastor'),
  getFinanceStats: () => api.get('/dashboard/finance'),
};

// Export api instance for direct use
export { api };

export default { api };

