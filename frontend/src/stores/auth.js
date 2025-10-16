import { defineStore } from 'pinia';
import { authAPI } from 'src/services/api';
import { Notify } from 'quasar';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: JSON.parse(localStorage.getItem('user')) || null,
    token: localStorage.getItem('jwt_token') || null,
    loading: false,
  }),

  getters: {
    isAuthenticated: (state) => !!state.token,
    
    userRoles: (state) => {
      return state.user?.roles || [];
    },
    
    hasRole: (state) => {
      return (roleName) => {
        return state.user?.roles?.some(role => role.name === roleName) || false;
      };
    },
    
    hasAnyRole: (state) => {
      return (roleNames) => {
        if (!Array.isArray(roleNames)) {
          roleNames = [roleNames];
        }
        return state.user?.roles?.some(role => roleNames.includes(role.name)) || false;
      };
    },
    
    isAdministrator: (state) => {
      return state.user?.roles?.some(role => role.name === 'administrator') || false;
    },
    
    isPastor: (state) => {
      return state.user?.roles?.some(role => role.name === 'pastor') || false;
    },
    
    isFinanceCommittee: (state) => {
      return state.user?.roles?.some(role => role.name === 'finance_committee') || false;
    },
    
    isMember: (state) => {
      return state.user?.roles?.some(role => role.name === 'user') || false;
    },

    primaryRole: (state) => {
      const roles = state.user?.roles || [];
      // Priority: administrator > pastor > finance_committee > user
      if (roles.some(r => r.name === 'administrator')) return 'administrator';
      if (roles.some(r => r.name === 'pastor')) return 'pastor';
      if (roles.some(r => r.name === 'finance_committee')) return 'finance_committee';
      return 'user';
    },
  },

  actions: {
    async login(credentials) {
      this.loading = true;
      try {
        const response = await authAPI.login(credentials);
        const { access_token, user } = response.data;
        
        this.token = access_token;
        this.user = user;
        
        localStorage.setItem('jwt_token', access_token);
        localStorage.setItem('user', JSON.stringify(user));
        
        Notify.create({
          type: 'positive',
          message: 'Login successful!',
          position: 'top',
        });
        
        return true;
      } catch (error) {
        const message = error.response?.data?.message || 'Login failed';
        Notify.create({
          type: 'negative',
          message,
          position: 'top',
        });
        return false;
      } finally {
        this.loading = false;
      }
    },

    async register(userData) {
      this.loading = true;
      try {
        const response = await authAPI.register(userData);
        const { token, user } = response.data;
        
        this.token = token;
        this.user = user;
        
        localStorage.setItem('jwt_token', token);
        localStorage.setItem('user', JSON.stringify(user));
        
        Notify.create({
          type: 'positive',
          message: 'Registration successful!',
          position: 'top',
        });
        
        return true;
      } catch (error) {
        const message = error.response?.data?.message || 'Registration failed';
        const errors = error.response?.data?.errors;
        
        if (errors) {
          Object.values(errors).forEach(errorArray => {
            errorArray.forEach(errorMessage => {
              Notify.create({
                type: 'negative',
                message: errorMessage,
                position: 'top',
              });
            });
          });
        } else {
          Notify.create({
            type: 'negative',
            message,
            position: 'top',
          });
        }
        
        return false;
      } finally {
        this.loading = false;
      }
    },

    async logout() {
      this.loading = true;
      try {
        await authAPI.logout();
      } catch (error) {
        console.error('Logout error:', error);
      } finally {
        this.token = null;
        this.user = null;
        localStorage.removeItem('jwt_token');
        localStorage.removeItem('user');
        this.loading = false;
        
        Notify.create({
          type: 'info',
          message: 'Logged out successfully',
          position: 'top',
        });
      }
    },

    async fetchUser() {
      try {
        const response = await authAPI.me();
        this.user = response.data.user;
        localStorage.setItem('user', JSON.stringify(response.data.user));
      } catch (error) {
        console.error('Fetch user error:', error);
        this.logout();
      }
    },

    async refreshToken() {
      try {
        const response = await authAPI.refresh();
        const { access_token, user } = response.data;
        
        this.token = access_token;
        this.user = user;
        
        localStorage.setItem('jwt_token', access_token);
        localStorage.setItem('user', JSON.stringify(user));
      } catch (error) {
        console.error('Refresh token error:', error);
        this.logout();
      }
    },
  },
});

