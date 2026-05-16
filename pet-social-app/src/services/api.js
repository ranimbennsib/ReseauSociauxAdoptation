import axios from 'axios'

const api = axios.create({
  baseURL: 'http://localhost:8000/api',
  headers: { 'Content-Type': 'application/json' },
  timeout: 10000,
})

api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('token')
    if (token) config.headers.Authorization = `Bearer ${token}`
    console.log(` ${config.method.toUpperCase()} ${config.baseURL}${config.url}`)
    return config
  },
  (error) => Promise.reject(error),
)

api.interceptors.response.use(
  (response) => {
    console.log(`📥 ${response.status} ${response.config.url}`)
    return response
  },
  (error) => {
    console.error(' API Error:', error.response?.status, error.response?.data)
    if (error.response?.status === 401) {
      localStorage.clear()
      window.location.href = '/login'
    }
    return Promise.reject(error)
  },
)

export const authAPI = {
  login: (data) => api.post('/auth/login', data),
  register: (data) => api.post('/auth/register', data),
}

export const postsAPI = {
  getAll: () => api.get('/posts'),
  getById: (id) => api.get(`/posts/${id}`),
  getMyPosts: () => api.get('/posts/my-posts'),
  getAdoption: () => api.get('/posts/adoption'),
  getDonation: () => api.get('/posts/donation'),
  getByCategory: (cat) => api.get(`/posts/category/${cat}`),
  getByCity: (city) => api.get(`/posts/city/${city}`),
  create: (data) => {
  // Si c'est une image (FormData), on change le type de contenu
  if (data instanceof FormData) {
    return api.post('/posts', data, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
  }
  // Sinon, c'est du texte normal
  return api.post('/posts', data)
},
  update: (id, data) => api.put(`/posts/${id}`, data),
  close: (id) => api.patch(`/posts/${id}/close`),
}

export const adoptionAPI = {
  create: (data) => api.post('/adoption_requests', data),
  getSent: () => api.get('/adoption_requests/sent'),
  getReceived: () => api.get('/adoption_requests/received'),
  getByPost: (postId) => api.get(`/adoption_requests/post/${postId}`),
  accept: (id) => api.patch(`/adoption_requests/${id}/accept`),
  reject: (id) => api.patch(`/adoption_requests/${id}/reject`),
  cancel: (id) => api.patch(`/adoption_requests/${id}/cancel`),
}

export const commentsAPI = {
  getByPost: (postId) => api.get(`/comments/post/${postId}`),
  getMyComments: () => api.get('/comments/my-comments'),
  countByPost: (postId) => api.get(`/comments/post/${postId}/count`),
  create: (data) => api.post('/comments', data),
  update: (id, content) =>
    api.patch(`/comments/${id}`, content, { headers: { 'Content-Type': 'text/plain' } }),
  delete: (id) => api.delete(`/comments/${id}`),
}

export const donationsAPI = {
  create: (data) => api.post('/donations', data),
  getMyDonations: () => api.get('/donations/my-donations'),
  getReceived: () => api.get('/donations/received'),
  getByPost: (postId) => api.get(`/donations/post/${postId}`),
  getTotalByPost: (postId) => api.get(`/donations/post/${postId}/total`),
  countByPost: (postId) => api.get(`/donations/post/${postId}/count`),
}

export const reportsAPI = {
  create: (data) => api.post('/reports', data),
}

export const adminAPI = {
  getAllUsers: () => api.get('/admin/users'),
  promoteUser: (id) => api.patch(`/admin/users/${id}/promote`),
  demoteUser: (id) => api.patch(`/admin/users/${id}/demote`),
  getAllPosts: () => api.get('/admin/posts'),
  getPostsByStatus: (status) => api.get(`/admin/posts/status/${status}`),
  getStatistics: () => api.get('/admin/statistics'),
  removePost: (id) => api.patch(`/posts/${id}/remove`),
  getAllReports: () => api.get('/reports'),
  getReportsByStatus: (status) => api.get(`/reports/status/${status}`),
  getReportsByPost: (postId) => api.get(`/reports/post/${postId}`),
  reviewReport: (id) => api.patch(`/reports/${id}/review`),
  resolveReport: (id) => api.patch(`/reports/${id}/resolve`),
  dismissReport: (id) => api.patch(`/reports/${id}/dismiss`),
}

export default api
