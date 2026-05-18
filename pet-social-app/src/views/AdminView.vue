<template>
  <div class="page">
    <NavBar />
    
    <div class="container">
      <div class="admin-header">
        <h1>Panel d'administration</h1>
        <div class="admin-tabs">
          <button 
            @click="activeTab = 'dashboard'" 
            :class="{ active: activeTab === 'dashboard' }"
          >
            📊 Dashboard
          </button>
          <button 
            @click="activeTab = 'users'; fetchUsers()" 
            :class="{ active: activeTab === 'users' }"
          >
            👥 Utilisateurs
          </button>
          <button 
            @click="activeTab = 'posts'; fetchPosts()" 
            :class="{ active: activeTab === 'posts' }"
          >
            📝 Posts
          </button>
          <button 
            @click="activeTab = 'reports'; fetchReports()" 
            :class="{ active: activeTab === 'reports' }"
          >
            ⚠️ Signalements
          </button>
        </div>
      </div>

      <!-- Dashboard -->
      <div v-if="activeTab === 'dashboard'" class="dashboard">
        <div v-if="loadingStats" class="loading">Chargement...</div>
        <div v-else>
          <div class="stats-grid">
            <div class="stat-card">
              <div class="stat-icon">👥</div>
              <div class="stat-info">
                <span class="stat-value">{{ stats.total_users }}</span>
                <span class="stat-label">Utilisateurs</span>
              </div>
            </div>
            <div class="stat-card">
              <div class="stat-icon">📝</div>
              <div class="stat-info">
                <span class="stat-value">{{ stats.total_posts }}</span>
                <span class="stat-label">Posts</span>
              </div>
            </div>
            <div class="stat-card">
              <div class="stat-icon">🐾</div>
              <div class="stat-info">
                <span class="stat-value">{{ stats.total_adoptions }}</span>
                <span class="stat-label">Adoptions</span>
              </div>
            </div>
            <div class="stat-card">
              <div class="stat-icon">💰</div>
              <div class="stat-info">
                <span class="stat-value">{{ stats.total_donations_made }} TND</span>
                <span class="stat-label">Dons reçus</span>
              </div>
            </div>
            <div class="stat-card">
              <div class="stat-icon">📋</div>
              <div class="stat-info">
                <span class="stat-value">{{ stats.total_adoption_requests }}</span>
                <span class="stat-label">Demandes d'adoption</span>
              </div>
            </div>
            <div class="stat-card">
              <div class="stat-icon">⚠️</div>
              <div class="stat-info">
                <span class="stat-value">{{ stats.pending_reports }}</span>
                <span class="stat-label">Signalements en attente</span>
              </div>
            </div>
          </div>

          <div class="charts-row">
            <div class="chart-card">
              <h3>Posts par statut</h3>
              <div class="status-bars">
                <div class="status-bar-item">
                  <span>Disponible</span>
                  <div class="bar">
                    <div class="bar-fill available" :style="{ width: getStatusPercent('available') + '%' }"></div>
                  </div>
                  <span>{{ stats.posts_by_status?.available || 0 }}</span>
                </div>
                <div class="status-bar-item">
                  <span>Adopté</span>
                  <div class="bar">
                    <div class="bar-fill adopted" :style="{ width: getStatusPercent('adopted') + '%' }"></div>
                  </div>
                  <span>{{ stats.posts_by_status?.adopted || 0 }}</span>
                </div>
                <div class="status-bar-item">
                  <span>Fermé</span>
                  <div class="bar">
                    <div class="bar-fill closed" :style="{ width: getStatusPercent('closed') + '%' }"></div>
                  </div>
                  <span>{{ stats.posts_by_status?.closed || 0 }}</span>
                </div>
                <div class="status-bar-item">
                  <span>Supprimé</span>
                  <div class="bar">
                    <div class="bar-fill removed" :style="{ width: getStatusPercent('removed') + '%' }"></div>
                  </div>
                  <span>{{ stats.posts_by_status?.removed || 0 }}</span>
                </div>
              </div>
            </div>
          </div>

          <div class="recent-section">
            <div class="recent-card">
              <h3>📝 Derniers posts</h3>
              <div class="recent-list">
                <div v-for="post in stats.recent_posts" :key="post.id" class="recent-item">
                  <span class="recent-title">{{ post.title }}</span>
                  <span class="recent-user">{{ post.user?.name }}</span>
                  <span class="recent-date">{{ formatDate(post.created_at) }}</span>
                </div>
              </div>
            </div>
            <div class="recent-card">
              <h3>👥 Nouveaux utilisateurs</h3>
              <div class="recent-list">
                <div v-for="user in stats.recent_users" :key="user.id" class="recent-item">
                  <span class="recent-title">{{ user.name }}</span>
                  <span class="recent-user">{{ user.email }}</span>
                  <span class="recent-date">{{ formatDate(user.created_at) }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Users Tab -->
<!-- Users Tab -->
<div v-if="activeTab === 'users'" class="users-section">
  <div class="section-header">
    <h2>Gestion des utilisateurs</h2>
    <div class="header-actions">
      <input 
        v-model="userSearch" 
        placeholder="🔍 Rechercher par nom, email..."
        class="search-input"
      />
    </div>
  </div>
  
  <div v-if="loadingUsers" class="loading">Chargement...</div>
  <div v-else-if="users.length === 0" class="empty">Aucun utilisateur trouvé.</div>
  
  <div v-else class="users-grid">
    <div v-for="user in filteredUsers" :key="user.id" class="user-card">
      <div class="user-card-header">
        <div class="user-avatar">
          <span class="avatar-initials">{{ getInitials(user.name) }}</span>
        </div>
        <span :class="['role-badge', user.role]">{{ user.role === 'admin' ? 'Admin' : 'Utilisateur' }}</span>
      </div>
      <div class="user-card-body">
        <h3 class="user-name">{{ user.name }}</h3>
        <p class="user-email">{{ user.email }}</p>
        <p class="user-phone">{{ user.phone || 'Téléphone non renseigné' }}</p>
      </div>
      <div class="user-card-footer">
        <div class="user-stats">
          <div class="stat">
            <span class="stat-number">{{ user.posts_count || 0 }}</span>
            <span class="stat-label">Posts</span>
          </div>
          <div class="stat-divider"></div>
          <div class="stat">
            <span class="stat-number">{{ user.adoption_requests_count || 0 }}</span>
            <span class="stat-label">Demandes</span>
          </div>
          <div class="stat-divider"></div>
          <div class="stat">
            <span class="stat-number">{{ user.donations_count || 0 }}</span>
            <span class="stat-label">Dons</span>
          </div>
        </div>
        <div class="user-actions">
          <button class="btn-view-user" @click="viewUserDetails(user)" title="Voir détails">
            <i class="bi bi-eye"></i> Détails
          </button>
          <button v-if="user.role !== 'admin'" class="btn-promote-user" @click="promoteUser(user.id)">
            <i class="bi bi-arrow-up-circle"></i> Admin
          </button>
          <button v-if="user.role === 'admin'" class="btn-demote-user" @click="demoteUser(user.id)">
            <i class="bi bi-arrow-down-circle"></i> User
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

      <!-- Posts Tab -->
      <div v-if="activeTab === 'posts'" class="posts-section">
        <div class="section-header">
          <h2>Gestion des posts</h2>
          <div class="post-filters">
            <select v-model="postStatusFilter">
              <option value="">Tous les statuts</option>
              <option value="available">Disponible</option>
              <option value="adopted">Adopté</option>
              <option value="closed">Fermé</option>
              <option value="removed">Supprimé</option>
            </select>
            <input v-model="postSearch" placeholder="🔍 Rechercher..." class="search-input" />
          </div>
        </div>
        <div v-if="loadingPosts" class="loading">Chargement...</div>
        <div v-else class="posts-table">
          <table>
            <thead>
              <tr>
                <th>Titre</th>
                <th>Auteur</th>
                <th>Catégorie</th>
                <th>Ville</th>
                <th>Statut</th>
                <th>Signalements</th>
                <th>Date</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="post in filteredPosts" :key="post.id">
                <td>{{ post.title }}</td>
                <td>{{ post.user?.name }}</td>
                <td>{{ post.category }}</td>
                <td>{{ post.city?.replace('_', ' ') }}</td>
                <td>
                  <span :class="['status-badge', post.status]">{{ post.status }}</span>
                </td>
                <td>{{ post.reports_count || 0 }}</td>
                <td>{{ formatDate(post.created_at) }}</td>
                <td class="actions-cell">
                  <button class="btn-view" @click="router.push(`/posts/${post.id}`)">Voir</button>
                  <button v-if="post.status !== 'removed'" class="btn-remove" @click="removePost(post.id)">
                    Supprimer
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Reports Tab -->
      <div v-if="activeTab === 'reports'" class="reports-section">
        <div class="section-header">
          <h2>Gestion des signalements</h2>
          <select v-model="reportStatusFilter">
            <option value="">Tous</option>
            <option value="pending">En attente</option>
            <option value="under_review">En cours</option>
            <option value="resolved">Résolu</option>
            <option value="dismissed">Ignoré</option>
          </select>
        </div>
        <div v-if="loadingReports" class="loading">Chargement...</div>
        <div v-else class="reports-list">
          <div v-for="report in filteredReports" :key="report.id" class="report-card">
            <div class="report-header">
              <div class="report-info">
                <span class="report-post">📝 {{ report.post?.title }}</span>
                <span class="report-reporter">Signalé par: {{ report.reporter?.name }}</span>
                <span :class="['report-status', report.status]">{{ getStatusLabel(report.status) }}</span>
              </div>
              <div class="report-date">{{ formatDate(report.created_at) }}</div>
            </div>
            <div class="report-reason">
              <strong>Motif:</strong> {{ report.reason }}
            </div>
            <div class="report-actions" v-if="report.status === 'pending'">
              <button class="btn-review" @click="reviewReport(report.id)">📋 Examiner</button>
              <button class="btn-resolve" @click="resolveReport(report.id)">🗑️ Supprimer le post</button>
              <button class="btn-dismiss" @click="dismissReport(report.id)">❌ Ignorer</button>
            </div>
            <div class="report-actions" v-else-if="report.status === 'under_review'">
              <button class="btn-resolve" @click="resolveReport(report.id)">🗑️ Supprimer le post</button>
              <button class="btn-dismiss" @click="dismissReport(report.id)">❌ Ignorer</button>
            </div>
          </div>
          <div v-if="filteredReports.length === 0" class="empty">Aucun signalement</div>
        </div>
      </div>
    </div>
    <!-- Modal Détails Utilisateur -->
<Teleport to="body">
  <div v-if="showUserDetailModal" class="modal-overlay" @click.self="closeUserDetailModal">
    <div class="modal-container">
      <div class="modal-header">
        <h3>Détails de l'utilisateur</h3>
        <button class="modal-close" @click="closeUserDetailModal">✕</button>
      </div>
      <div class="modal-body" v-if="selectedUser">
        <div class="user-detail-header">
          <div class="user-detail-avatar">
            <span class="avatar-initials">{{ getInitials(selectedUser.name) }}</span>
          </div>
          <div class="user-detail-info">
            <h2>{{ selectedUser.name }}</h2>
            <p>{{ selectedUser.email }}</p>
          </div>
        </div>
        
        <div class="detail-grid">
          <div class="detail-card">
            <label>ID Utilisateur</label>
            <p class="detail-value">{{ selectedUser.id }}</p>
          </div>
          <div class="detail-card">
            <label>Téléphone</label>
            <p class="detail-value">{{ selectedUser.phone || 'Non renseigné' }}</p>
          </div>
          <div class="detail-card">
            <label>Rôle</label>
            <p class="detail-value">{{ selectedUser.role === 'admin' ? 'Administrateur' : 'Utilisateur' }}</p>
          </div>
          <div class="detail-card">
            <label>Date d'inscription</label>
            <p class="detail-value">{{ formatDate(selectedUser.created_at) }}</p>
          </div>
        </div>
        
        <div class="stats-detail">
          <h4>📊 Statistiques d'activité</h4>
          <div class="stats-detail-grid">
            <div class="stat-detail-item">
              <span class="stat-detail-number">{{ userStats.posts_count }}</span>
              <span class="stat-detail-label">Posts publiés</span>
            </div>
            <div class="stat-detail-item">
              <span class="stat-detail-number">{{ userStats.adoption_requests_count }}</span>
              <span class="stat-detail-label">Demandes d'adoption</span>
            </div>
            <div class="stat-detail-item">
              <span class="stat-detail-number">{{ userStats.donations_count }}</span>
              <span class="stat-detail-label">Dons effectués</span>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn-view-user" @click="closeUserDetailModal">Fermer</button>
        <button v-if="selectedUser?.role !== 'admin'" class="btn-promote-user" @click="promoteUser(selectedUser.id); closeUserDetailModal()">
          Promouvoir Admin
        </button>
        <button v-if="selectedUser?.role === 'admin'" class="btn-demote-user" @click="demoteUser(selectedUser.id); closeUserDetailModal()">
          Rétrograder User
        </button>
      </div>
    </div>
  </div>
</Teleport>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { adminAPI } from '../services/api'
import NavBar from '../components/NavBar.vue'

const router = useRouter()

// État
const activeTab = ref('dashboard')
const loadingStats = ref(false)
const loadingUsers = ref(false)
const loadingPosts = ref(false)
const loadingReports = ref(false)

// Données
const stats = ref({})
const users = ref([])
const posts = ref([])
const reports = ref([])

// Filtres
const userSearch = ref('')
const postSearch = ref('')
const postStatusFilter = ref('')
const reportStatusFilter = ref('')

// Utilisateurs filtrés
const filteredUsers = computed(() => {
  if (!userSearch.value) return users.value
  const query = userSearch.value.toLowerCase()
  return users.value.filter(u => 
    u.name.toLowerCase().includes(query) || 
    u.email.toLowerCase().includes(query)
  )
})
// États pour le modal de détails
const showUserDetailModal = ref(false)
const selectedUser = ref(null)
const userStats = ref({})

// Méthode pour afficher les détails d'un utilisateur
const viewUserDetails = async (user) => {
  selectedUser.value = user
  showUserDetailModal.value = true
  userStats.value = {
    posts_count: user.posts_count || 0,
    adoption_requests_count: user.adoption_requests_count || 0,
    donations_count: user.donations_count || 0
  }
}

const closeUserDetailModal = () => {
  showUserDetailModal.value = false
  selectedUser.value = null
  userStats.value = {}
}

const getInitials = (name) => {
  if (!name) return '?'
  return name.substring(0, 2).toUpperCase()
}
// Posts filtrés
const filteredPosts = computed(() => {
  let result = posts.value
  if (postStatusFilter.value) {
    result = result.filter(p => p.status === postStatusFilter.value)
  }
  if (postSearch.value) {
    const query = postSearch.value.toLowerCase()
    result = result.filter(p => 
      p.title.toLowerCase().includes(query) ||
      p.user?.name.toLowerCase().includes(query)
    )
  }
  return result
})

// Signalements filtrés
const filteredReports = computed(() => {
  if (!reportStatusFilter.value) return reports.value
  return reports.value.filter(r => r.status === reportStatusFilter.value)
})

// Méthodes
const formatDate = (date) => date ? new Date(date).toLocaleDateString('fr-FR') : ''

const getStatusPercent = (status) => {
  const total = stats.value.total_posts || 1
  const count = stats.value.posts_by_status?.[status] || 0
  return (count / total) * 100
}

const getStatusLabel = (status) => {
  const labels = {
    pending: 'En attente',
    under_review: 'En cours',
    resolved: 'Résolu',
    dismissed: 'Ignoré'
  }
  return labels[status] || status
}

// Dashboard
const fetchStats = async () => {
  loadingStats.value = true
  try {
    const res = await adminAPI.getStatistics()
    stats.value = res.data
  } catch (error) {
    console.error('Erreur chargement stats:', error)
  } finally {
    loadingStats.value = false
  }
}

// Utilisateurs
const fetchUsers = async () => {
  loadingUsers.value = true
  try {
    const res = await adminAPI.getAllUsers()
    users.value = res.data
  } catch (error) {
    console.error('Erreur chargement users:', error)
  } finally {
    loadingUsers.value = false
  }
}

const promoteUser = async (id) => {
  if (!confirm('Promouvoir cet utilisateur en administrateur ?')) return
  try {
    await adminAPI.promoteUser(id)
    await fetchUsers()
  } catch (error) {
    alert(error.response?.data?.message || 'Erreur')
  }
}

const demoteUser = async (id) => {
  if (!confirm('Rétrograder cet administrateur en utilisateur normal ?')) return
  try {
    await adminAPI.demoteUser(id)
    await fetchUsers()
  } catch (error) {
    alert(error.response?.data?.message || 'Erreur')
  }
}

// Posts
const fetchPosts = async () => {
  loadingPosts.value = true
  try {
    const res = await adminAPI.getAllPosts()
    posts.value = res.data
  } catch (error) {
    console.error('Erreur chargement posts:', error)
  } finally {
    loadingPosts.value = false
  }
}

const removePost = async (id) => {
  if (!confirm('Supprimer ce post définitivement ?')) return
  try {
    await adminAPI.removePost(id)
    await fetchPosts()
    await fetchStats()
  } catch (error) {
    alert(error.response?.data?.message || 'Erreur')
  }
}

// Signalements
const fetchReports = async () => {
  loadingReports.value = true
  try {
    const res = await adminAPI.getAllReports()
    reports.value = res.data
  } catch (error) {
    console.error('Erreur chargement reports:', error)
  } finally {
    loadingReports.value = false
  }
}

const reviewReport = async (id) => {
  try {
    await adminAPI.reviewReport(id)
    await fetchReports()
    await fetchStats()
  } catch (error) {
    alert(error.response?.data?.message || 'Erreur')
  }
}

const resolveReport = async (id) => {
  if (!confirm('Supprimer le post et marquer le signalement comme résolu ?')) return
  try {
    await adminAPI.resolveReport(id)
    await fetchReports()
    await fetchPosts()
    await fetchStats()
  } catch (error) {
    alert(error.response?.data?.message || 'Erreur')
  }
}

const dismissReport = async (id) => {
  if (!confirm('Ignorer ce signalement ?')) return
  try {
    await adminAPI.dismissReport(id)
    await fetchReports()
    await fetchStats()
  } catch (error) {
    alert(error.response?.data?.message || 'Erreur')
  }
}

onMounted(() => {
  fetchStats()
})
</script>

<style scoped>
/* ===== USERS GRID ===== */
.users-section .section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
  flex-wrap: wrap;
  gap: 1rem;
}

.users-section .section-header h2 {
  font-size: 1.5rem;
  font-weight: 600;
  color: #1a1a2e;
}

.header-actions {
  display: flex;
  gap: 1rem;
}

.users-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 1.5rem;
}

.user-card {
  background: #fff;
  border-radius: 16px;
  overflow: hidden;
  transition: all 0.3s ease;
  border: 1px solid #ede9e0;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}

.user-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 24px rgba(0, 0, 0, 0.08);
  border-color: #d4c8b8;
}

.user-card-header {
  background: linear-gradient(135deg, #faf8f0 0%, #f5f0e8 100%);
  padding: 1.25rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid #ede9e0;
}

.user-avatar {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  background: #e8e0d0;
  display: flex;
  align-items: center;
  justify-content: center;
}

.avatar-initials {
  font-size: 1.2rem;
  font-weight: 700;
  color: #a08050;
  text-transform: uppercase;
}

.role-badge {
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-size: 0.7rem;
  font-weight: 600;
  text-transform: uppercase;
}

.role-badge.admin {
  background: #1a1a2e;
  color: #fff;
}

.role-badge.user {
  background: #e8f4ff;
  color: #2b7de9;
}

.user-card-body {
  padding: 1.25rem;
}

.user-name {
  font-size: 1.1rem;
  font-weight: 600;
  color: #1a1a2e;
  margin-bottom: 0.5rem;
}

.user-email {
  font-size: 0.85rem;
  color: #888;
  margin-bottom: 0.3rem;
}

.user-phone {
  font-size: 0.85rem;
  color: #aaa;
  margin-bottom: 0;
}

.user-card-footer {
  padding: 1rem 1.25rem;
  background: #fafaf8;
  border-top: 1px solid #f0ece4;
}

.user-stats {
  display: flex;
  justify-content: space-around;
  align-items: center;
  margin-bottom: 1rem;
}

.stat {
  text-align: center;
}

.stat-number {
  display: block;
  font-size: 1.3rem;
  font-weight: 700;
  color: #c8a84b;
}

.stat-label {
  font-size: 0.7rem;
  color: #aaa;
  text-transform: uppercase;
}

.stat-divider {
  width: 1px;
  height: 30px;
  background: #ede9e0;
}

.user-actions {
  display: flex;
  gap: 0.5rem;
  justify-content: center;
}

.btn-view-user, .btn-promote-user, .btn-demote-user {
  padding: 0.4rem 0.8rem;
  border: none;
  border-radius: 6px;
  font-size: 0.75rem;
  cursor: pointer;
  transition: all 0.2s;
  display: inline-flex;
  align-items: center;
  gap: 4px;
}

.btn-view-user {
  background: #f5f0e8;
  color: #888;
}

.btn-view-user:hover {
  background: #e8e0d0;
}

.btn-promote-user {
  background: #e8faf0;
  color: #3a9e68;
}

.btn-promote-user:hover {
  background: #d0f0e0;
}

.btn-demote-user {
  background: #fff0f0;
  color: #e05252;
}

.btn-demote-user:hover {
  background: #f0e0e0;
}

/* ===== MODAL DÉTAILS UTILISATEUR ===== */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-container {
  background: #fff;
  border-radius: 20px;
  width: 90%;
  max-width: 700px;
  max-height: 85vh;
  overflow-y: auto;
  animation: modalSlideIn 0.3s ease;
}

@keyframes modalSlideIn {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.modal-header {
  background: linear-gradient(135deg, #faf8f0 0%, #f5f0e8 100%);
  padding: 1.5rem 2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid #ede9e0;
}

.modal-header h3 {
  font-size: 1.3rem;
  font-weight: 600;
  color: #1a1a2e;
  margin: 0;
}

.modal-close {
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: #aaa;
  transition: color 0.2s;
}

.modal-close:hover {
  color: #1a1a2e;
}

.modal-body {
  padding: 2rem;
}

.user-detail-header {
  display: flex;
  align-items: center;
  gap: 1.5rem;
  margin-bottom: 2rem;
  padding-bottom: 1.5rem;
  border-bottom: 1px solid #ede9e0;
}

.user-detail-avatar {
  width: 80px;
  height: 80px;
  border-radius: 20px;
  background: #e8e0d0;
  display: flex;
  align-items: center;
  justify-content: center;
}

.user-detail-avatar .avatar-initials {
  font-size: 2rem;
}

.user-detail-info h2 {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1a1a2e;
  margin-bottom: 0.25rem;
}

.user-detail-info p {
  color: #888;
  margin: 0;
}

.detail-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
  margin-bottom: 2rem;
}

.detail-card {
  background: #fafaf8;
  border: 1px solid #ede9e0;
  border-radius: 12px;
  padding: 1rem;
}

.detail-card label {
  font-size: 0.7rem;
  color: #aaa;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  display: block;
  margin-bottom: 0.3rem;
}

.detail-card .detail-value {
  font-size: 0.95rem;
  font-weight: 500;
  color: #1a1a2e;
  margin: 0;
}

.stats-detail {
  background: #fafaf8;
  border-radius: 12px;
  padding: 1rem;
  margin-bottom: 2rem;
}

.stats-detail h4 {
  font-size: 0.9rem;
  color: #888;
  margin-bottom: 1rem;
  padding-bottom: 0.5rem;
  border-bottom: 1px solid #ede9e0;
}

.stats-detail-grid {
  display: flex;
  justify-content: space-around;
  text-align: center;
}

.stat-detail-item {
  text-align: center;
}

.stat-detail-number {
  display: block;
  font-size: 1.8rem;
  font-weight: 700;
  color: #c8a84b;
}

.stat-detail-label {
  font-size: 0.75rem;
  color: #aaa;
}

.modal-footer {
  padding: 1rem 2rem 1.5rem;
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  border-top: 1px solid #ede9e0;
}

.page {
  min-height: 100vh;
  background: #fafaf8;
}
.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 2rem;
}
.admin-header {
  margin-bottom: 2rem;
}
.admin-header h1 {
  font-family: 'Playfair Display', serif;
  font-size: 2rem;
  color: #1a1a2e;
  margin-bottom: 1.5rem;
}
.admin-tabs {
  display: flex;
  gap: 0.5rem;
  border-bottom: 1px solid #ede9e0;
  padding-bottom: 0.5rem;
}
.admin-tabs button {
  padding: 0.6rem 1.5rem;
  background: none;
  border: none;
  font-size: 0.85rem;
  cursor: pointer;
  border-radius: 6px;
  transition: all 0.2s;
}
.admin-tabs button:hover {
  background: #f5f0e8;
}
.admin-tabs button.active {
  background: #1a1a2e;
  color: #fff;
}

/* Dashboard */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 1rem;
  margin-bottom: 2rem;
}
.stat-card {
  background: #fff;
  border: 1px solid #ede9e0;
  border-radius: 12px;
  padding: 1.2rem;
  display: flex;
  align-items: center;
  gap: 1rem;
}
.stat-icon {
  font-size: 2rem;
}
.stat-info {
  display: flex;
  flex-direction: column;
}
.stat-value {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1a1a2e;
}
.stat-label {
  font-size: 0.75rem;
  color: #aaa;
}
.charts-row {
  margin-bottom: 2rem;
}
.chart-card {
  background: #fff;
  border: 1px solid #ede9e0;
  border-radius: 12px;
  padding: 1.5rem;
}
.chart-card h3 {
  margin-bottom: 1rem;
  font-size: 1rem;
}
.status-bars {
  display: flex;
  flex-direction: column;
  gap: 0.8rem;
}
.status-bar-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  font-size: 0.85rem;
}
.status-bar-item span:first-child {
  width: 80px;
}
.status-bar-item .bar {
  flex: 1;
  height: 8px;
  background: #f0ece4;
  border-radius: 4px;
  overflow: hidden;
}
.bar-fill {
  height: 100%;
  border-radius: 4px;
}
.bar-fill.available { background: #3a9e68; }
.bar-fill.adopted { background: #2b7de9; }
.bar-fill.closed { background: #888; }
.bar-fill.removed { background: #e05252; }
.recent-section {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.5rem;
}
.recent-card {
  background: #fff;
  border: 1px solid #ede9e0;
  border-radius: 12px;
  padding: 1.5rem;
}
.recent-card h3 {
  margin-bottom: 1rem;
  font-size: 1rem;
}
.recent-list {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}
.recent-item {
  display: flex;
  justify-content: space-between;
  padding: 0.5rem 0;
  border-bottom: 1px solid #f5f0e8;
  font-size: 0.85rem;
}
.recent-title {
  font-weight: 500;
  color: #1a1a2e;
}
.recent-user, .recent-date {
  color: #888;
  font-size: 0.75rem;
}

/* Tables */
.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
  flex-wrap: wrap;
  gap: 1rem;
}
.section-header h2 {
  font-size: 1.2rem;
}
.search-input, .post-filters select, .reports-section select {
  padding: 0.5rem 1rem;
  border: 1px solid #e8e3d8;
  border-radius: 8px;
  font-size: 0.85rem;
}
.post-filters {
  display: flex;
  gap: 1rem;
}
table {
  width: 100%;
  background: #fff;
  border: 1px solid #ede9e0;
  border-radius: 12px;
  overflow: hidden;
}
th, td {
  padding: 1rem;
  text-align: left;
  border-bottom: 1px solid #f5f0e8;
}
th {
  background: #fafaf8;
  font-weight: 600;
  font-size: 0.8rem;
  color: #888;
}
td {
  font-size: 0.85rem;
}
.role-badge, .status-badge {
  display: inline-block;
  padding: 0.2rem 0.5rem;
  border-radius: 4px;
  font-size: 0.7rem;
  font-weight: 600;
}
.role-badge.admin, .status-badge.admin {
  background: #1a1a2e;
  color: #fff;
}
.role-badge.user {
  background: #e8f4ff;
  color: #2b7de9;
}
.status-badge.available { background: #e8faf0; color: #3a9e68; }
.status-badge.adopted { background: #e8f4ff; color: #2b7de9; }
.status-badge.closed { background: #f0ece4; color: #888; }
.status-badge.removed { background: #fff0f0; color: #e05252; }

.actions-cell {
  display: flex;
  gap: 0.5rem;
}
.btn-promote, .btn-demote, .btn-view, .btn-remove {
  padding: 0.3rem 0.8rem;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 0.75rem;
}
.btn-promote { background: #e8faf0; color: #3a9e68; }
.btn-demote { background: #fff0f0; color: #e05252; }
.btn-view { background: #f0ece4; color: #888; }
.btn-remove { background: #fff0f0; color: #e05252; }

/* Reports */
.reports-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}
.report-card {
  background: #fff;
  border: 1px solid #ede9e0;
  border-radius: 12px;
  padding: 1.2rem;
}
.report-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.8rem;
}
.report-info {
  display: flex;
  gap: 1rem;
  align-items: center;
  flex-wrap: wrap;
}
.report-post {
  font-weight: 600;
  color: #1a1a2e;
}
.report-reporter {
  font-size: 0.8rem;
  color: #888;
}
.report-status {
  padding: 0.2rem 0.5rem;
  border-radius: 4px;
  font-size: 0.7rem;
  font-weight: 600;
}
.report-status.pending { background: #fff8e0; color: #c8a84b; }
.report-status.under_review { background: #e8f4ff; color: #2b7de9; }
.report-status.resolved { background: #e8faf0; color: #3a9e68; }
.report-status.dismissed { background: #f0ece4; color: #888; }
.report-date {
  font-size: 0.75rem;
  color: #aaa;
}
.report-reason {
  font-size: 0.85rem;
  color: #666;
  margin-bottom: 1rem;
  padding: 0.5rem;
  background: #fafaf8;
  border-radius: 8px;
}
.report-actions {
  display: flex;
  gap: 0.5rem;
}
.btn-review, .btn-resolve, .btn-dismiss {
  padding: 0.4rem 1rem;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 0.8rem;
}
.btn-review { background: #f0ece4; color: #888; }
.btn-resolve { background: #fff0f0; color: #e05252; }
.btn-dismiss { background: #f0f0f0; color: #aaa; }

.loading, .empty {
  text-align: center;
  padding: 3rem;
  color: #aaa;
}
</style>