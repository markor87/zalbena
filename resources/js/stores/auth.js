import { defineStore } from 'pinia';
import axios from 'axios';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    isInitialized: false,
  }),

  getters: {
    isAuthenticated: (state) => !!state.user,
    currentUser: (state) => state.user,
  },

  actions: {
    async login(credentials) {
      try {
        const response = await axios.post('/login', credentials);
        this.user = response.data.user;
        return response.data;
      } catch (error) {
        throw error;
      }
    },

    async logout() {
      try {
        await axios.post('/logout');
      } catch (error) {
        console.error('Logout error:', error);
      } finally {
        this.user = null;
      }
    },

    async fetchUser() {
      try {
        const response = await axios.get('/user');
        this.user = response.data;
      } catch (error) {
        this.user = null;
      } finally {
        this.isInitialized = true;
      }
    },

    async initAuth() {
      await this.fetchUser();
    }
  }
});
