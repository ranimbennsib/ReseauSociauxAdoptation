import { defineStore } from 'pinia'
import { authAPI } from '../services/api' // ✅ was '../services/axios'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: JSON.parse(localStorage.getItem('user')) || null, // ✅ restore user on reload
    token: localStorage.getItem('token'),
    loading: false,
    error: null,
  }),

  getters: {
    isAuthenticated: (state) => !!state.token,
    isAdmin: (state) => state.user?.role === 'admin',
    userName: (state) => state.user?.name || '',
    userEmail: (state) => state.user?.email || '',
    userRole: (state) => state.user?.role || '',
  },

  actions: {
    async register(userData) {
      this.loading = true
      this.error = null
      try {
        const response = await authAPI.register(userData)
        if (response.data.token) {
          this.token = response.data.token
          this.user = {
            id: response.data.id,
            name: response.data.name,
            email: response.data.email,
            role: response.data.role,
          }
          localStorage.setItem('token', response.data.token)
          localStorage.setItem('user', JSON.stringify(this.user))
          return { success: true, data: response.data }
        }
        return { success: false, message: "Erreur lors de l'inscription" }
      } catch (error) {
        const message =
          error.response?.data?.message || error.message || "Erreur lors de l'inscription"
        this.error = message
        return { success: false, message }
      } finally {
        this.loading = false
      }
    },

    async login(credentials) {
      this.loading = true
      this.error = null
      try {
        const response = await authAPI.login(credentials)
        if (response.data.token) {
          this.token = response.data.token
          this.user = {
            id: response.data.id,
            name: response.data.name,
            email: response.data.email,
            role: response.data.role,
          }
          localStorage.setItem('token', response.data.token)
          localStorage.setItem('user', JSON.stringify(this.user))
          return { success: true, data: response.data }
        }
        return { success: false, message: 'Email ou mot de passe incorrect' }
      } catch (error) {
        const message =
          error.response?.data?.message || error.message || 'Email ou mot de passe incorrect'
        this.error = message
        return { success: false, message }
      } finally {
        this.loading = false
      }
    },

    logout() {
      this.token = null
      this.user = null
      localStorage.removeItem('token')
      localStorage.removeItem('user')
    },

    checkAuth() {
      const token = localStorage.getItem('token')
      const user = localStorage.getItem('user')
      if (token && user) {
        this.token = token
        this.user = JSON.parse(user)
        return true
      }
      return false
    },
  },
})
