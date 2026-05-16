<template>
  <div class="page">
    <NavBar />
    <div class="container">
      <h1>Administration</h1>

      <!-- Stats -->
      <div class="stats-grid" v-if="stats">
        <div class="stat-card" v-for="(value, key) in stats" :key="key">
          <div class="stat-value">{{ value }}</div>
          <div class="stat-label">{{ statLabel(key) }}</div>
        </div>
      </div>

      <!-- Tabs -->
      <div class="tabs">
        <button :class="{ active: tab === 'users' }" @click="tab = 'users'">Utilisateurs</button>
        <button :class="{ active: tab === 'posts' }" @click="tab = 'posts'">Posts</button>
        <button :class="{ active: tab === 'reports' }" @click="tab = 'reports'">
          Signalements
        </button>
      </div>

      <!-- Users -->
      <div v-if="tab === 'users'">
        <div v-for="user in users" :key="user.id" class="admin-row">
          <div>
            <strong>{{ user.name }}</strong>
            <span class="role-badge" :class="`role-${user.role}`">{{ user.role }}</span>
            <span class="meta">{{ user.email }}</span>
          </div>
          <div class="row-actions">
            <button
              v-if="user.role === 'user'"
              class="btn-sm btn-promote"
              @click="promote(user.id)"
            >
              Promouvoir
            </button>
            <button v-if="user.role === 'admin'" class="btn-sm btn-demote" @click="demote(user.id)">
              Rétrograder
            </button>
          </div>
        </div>
      </div>

      <!-- Posts -->
      <div v-if="tab === 'posts'">
        <div v-for="post in adminPosts" :key="post.id" class="admin-row">
          <div>
            <strong>{{ post.title }}</strong>
            <span class="post-status" :class="`status-${post.status}`">{{ post.status }}</span>
            <span class="meta">{{ post.user?.name }} · {{ post.city }}</span>
          </div>
          <div class="row-actions">
            <button
              v-if="post.status !== 'removed'"
              class="btn-sm btn-remove"
              @click="removePost(post.id)"
            >
              Supprimer
            </button>
          </div>
        </div>
      </div>

      <!-- Reports -->
      <div v-if="tab === 'reports'">
        <div v-for="report in reports" :key="report.id" class="admin-row">
          <div>
            <strong>{{ report.post?.title }}</strong>
            <span class="req-status" :class="`status-${report.status}`">{{ report.status }}</span>
            <span class="meta">Signalé par: {{ report.reporter?.name }} — {{ report.reason }}</span>
          </div>
          <div class="row-actions">
            <button v-if="report.status === 'pending'" class="btn-sm" @click="review(report.id)">
              Examiner
            </button>
            <button
              v-if="report.status !== 'resolved' && report.status !== 'dismissed'"
              class="btn-sm btn-remove"
              @click="resolve(report.id)"
            >
              Résoudre
            </button>
            <button
              v-if="report.status !== 'dismissed' && report.status !== 'resolved'"
              class="btn-sm btn-demote"
              @click="dismiss(report.id)"
            >
              Ignorer
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { adminAPI } from '../services/api'
import NavBar from '../components/NavBar.vue'

const tab = ref('users')
const stats = ref(null)
const users = ref([])
const adminPosts = ref([])
const reports = ref([])

const statLabel = (key) =>
  ({
    totalUsers: 'Utilisateurs',
    totalPosts: 'Posts',
    availablePosts: 'Disponibles',
    adoptedPosts: 'Adoptés',
    removedPosts: 'Supprimés',
    totalReports: 'Signalements',
    pendingReports: 'En attente',
    totalDonations: 'Dons',
    totalAdoptionRequests: 'Demandes',
  })[key] || key

onMounted(async () => {
  const res = await adminAPI.getStatistics()
  stats.value = res.data
  loadTab()
})

const loadTab = async () => {
  if (tab.value === 'users') {
    const res = await adminAPI.getAllUsers()
    users.value = res.data
  } else if (tab.value === 'posts') {
    const res = await adminAPI.getAllPosts()
    adminPosts.value = res.data
  } else {
    const res = await adminAPI.getAllReports()
    reports.value = res.data
  }
}

watch(tab, loadTab)

const promote = async (id) => {
  await adminAPI.promoteUser(id)
  loadTab()
}
const demote = async (id) => {
  await adminAPI.demoteUser(id)
  loadTab()
}
const removePost = async (id) => {
  await adminAPI.removePost(id)
  loadTab()
}
const review = async (id) => {
  await adminAPI.reviewReport(id)
  loadTab()
}
const resolve = async (id) => {
  await adminAPI.resolveReport(id)
  loadTab()
}
const dismiss = async (id) => {
  await adminAPI.dismissReport(id)
  loadTab()
}
</script>

<style scoped>
.page {
  min-height: 100vh;
  background: #fafaf8;
}
.container {
  max-width: 1000px;
  margin: 0 auto;
  padding: 2rem 2.5rem;
}
h1 {
  font-family: 'Playfair Display', serif;
  font-size: 1.8rem;
  color: #1a1a2e;
  margin-bottom: 1.5rem;
}
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
  gap: 1rem;
  margin-bottom: 2rem;
}
.stat-card {
  background: #fff;
  border: 1px solid #ede9e0;
  border-radius: 8px;
  padding: 1.25rem;
  text-align: center;
}
.stat-value {
  font-size: 1.8rem;
  font-weight: 700;
  color: #c8a84b;
}
.stat-label {
  font-size: 0.72rem;
  color: #aaa;
  margin-top: 0.25rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}
.tabs {
  display: flex;
  gap: 0.5rem;
  margin-bottom: 1.5rem;
}
.tabs button {
  padding: 0.45rem 1.2rem;
  border: 1px solid #e5e0d5;
  border-radius: 20px;
  background: #fff;
  font-size: 0.82rem;
  cursor: pointer;
  transition: all 0.2s;
}
.tabs button.active {
  background: #1a1a2e;
  color: #fff;
  border-color: #1a1a2e;
}
.admin-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: #fff;
  border: 1px solid #ede9e0;
  border-radius: 7px;
  padding: 0.9rem 1.25rem;
  margin-bottom: 0.5rem;
}
.admin-row strong {
  font-size: 0.9rem;
  color: #1a1a2e;
  margin-right: 0.5rem;
}
.meta {
  font-size: 0.78rem;
  color: #aaa;
  display: block;
  margin-top: 0.2rem;
}
.role-badge {
  font-size: 0.6rem;
  font-weight: 600;
  padding: 0.15rem 0.45rem;
  border-radius: 3px;
  text-transform: uppercase;
  margin-right: 0.4rem;
}
.role-user {
  background: #e8f4ff;
  color: #2b7de9;
}
.role-admin {
  background: #fff3e0;
  color: #e07b2b;
}
.post-status,
.req-status {
  font-size: 0.6rem;
  font-weight: 600;
  padding: 0.15rem 0.45rem;
  border-radius: 3px;
  text-transform: uppercase;
  margin-right: 0.4rem;
}
.status-available {
  background: #e8faf0;
  color: #3a9e68;
}
.status-adopted {
  background: #e8f4ff;
  color: #2b7de9;
}
.status-removed {
  background: #fff0f0;
  color: #e05252;
}
.status-pending {
  background: #fff8e0;
  color: #c8a84b;
}
.status-under_review {
  background: #f0e8ff;
  color: #7b2be0;
}
.status-resolved {
  background: #e8faf0;
  color: #3a9e68;
}
.status-dismissed {
  background: #f0ece4;
  color: #888;
}
.row-actions {
  display: flex;
  gap: 0.4rem;
}
.btn-sm {
  padding: 0.35rem 0.8rem;
  border: none;
  border-radius: 4px;
  font-size: 0.75rem;
  cursor: pointer;
  background: #f0ece4;
  color: #555;
  transition: all 0.2s;
}
.btn-sm:hover {
  background: #1a1a2e;
  color: #fff;
}
.btn-promote {
  background: #e8f4ff;
  color: #2b7de9;
}
.btn-demote {
  background: #fff8e0;
  color: #c8a84b;
}
.btn-remove {
  background: #fff0f0;
  color: #e05252;
}
</style>
