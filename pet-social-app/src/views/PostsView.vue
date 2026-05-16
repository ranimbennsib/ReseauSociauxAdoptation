<template>
  <div class="page">
    <NavBar />
    <div class="container">
      <div class="page-header">
        <h1>Tous les animaux</h1>
        <div class="filters">
          <button :class="{ active: filter === 'all' }" @click="filter = 'all'">Tous</button>
          <button :class="{ active: filter === 'adoption' }" @click="filter = 'adoption'">
            Adoption
          </button>
          <button :class="{ active: filter === 'donation' }" @click="filter = 'donation'">
            Dons
          </button>
        </div>
      </div>

      <div v-if="loading" class="loading">Chargement...</div>
      <div v-else-if="error" class="alert alert-error">{{ error }}</div>
      <div v-else class="posts-grid">
        <div
          v-for="post in posts"
          :key="post.id"
          class="post-card"
          @click="router.push(`/posts/${post.id}`)"
        >
          <div class="post-img">
            <img v-if="post.image" :src="post.image" :alt="post.title" />
            <div v-else class="post-img-placeholder">🐾</div>
          </div>
          <div class="post-body">
            <div class="post-tags">
              <span v-if="post.isAdoption" class="tag tag-adoption">Adoption</span>
              <span v-if="post.isDonation" class="tag tag-donation">Don</span>
              <span class="tag tag-city">{{ post.city?.replace('_', ' ') }}</span>
            </div>
            <h3>{{ post.title }}</h3>
            <p>{{ post.description?.substring(0, 80) }}...</p>
            <div class="post-footer">
              <span class="post-category">{{ post.category }}</span>
              <span class="post-date">{{ formatDate(post.createdAt) }}</span>
            </div>
          </div>
        </div>
      </div>

      <div v-if="!loading && posts.length === 0" class="empty">
        Aucun animal disponible pour le moment.
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { postsAPI } from '../services/api'
import NavBar from '../components/NavBar.vue'

const router = useRouter()
const posts = ref([])
const loading = ref(false)
const error = ref('')
const filter = ref('all')

const fetchPosts = async () => {
  loading.value = true
  error.value = ''
  try {
    let res
    if (filter.value === 'adoption') res = await postsAPI.getAdoption()
    else if (filter.value === 'donation') res = await postsAPI.getDonation()
    else res = await postsAPI.getAll()
    posts.value = res.data
  } catch (e) {
    error.value = 'Erreur lors du chargement des posts'
  } finally {
    loading.value = false
  }
}

const formatDate = (date) => (date ? new Date(date).toLocaleDateString('fr-FR') : '')

watch(filter, fetchPosts)
onMounted(fetchPosts)
</script>

<style scoped>
.page {
  min-height: 100vh;
  background: #fafaf8;
}
.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 2rem 2.5rem;
}
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}
.page-header h1 {
  font-family: 'Playfair Display', serif;
  font-size: 2rem;
  color: #1a1a2e;
}
.filters {
  display: flex;
  gap: 0.5rem;
}
.filters button {
  padding: 0.4rem 1rem;
  border: 1px solid #e5e0d5;
  border-radius: 20px;
  background: #fff;
  font-size: 0.8rem;
  cursor: pointer;
  transition: all 0.2s;
}
.filters button.active,
.filters button:hover {
  background: #1a1a2e;
  color: #fff;
  border-color: #1a1a2e;
}
.posts-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 1.5rem;
}
.post-card {
  background: #fff;
  border: 1px solid #ede9e0;
  border-radius: 10px;
  overflow: hidden;
  cursor: pointer;
  transition:
    transform 0.2s,
    box-shadow 0.2s;
}
.post-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 30px rgba(0, 0, 0, 0.08);
}
.post-img {
  height: 180px;
  overflow: hidden;
  background: #fdf5e8;
}
.post-img img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
.post-img-placeholder {
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 3rem;
}
.post-body {
  padding: 1rem;
}
.post-tags {
  display: flex;
  gap: 0.4rem;
  flex-wrap: wrap;
  margin-bottom: 0.5rem;
}
.tag {
  font-size: 0.65rem;
  font-weight: 600;
  padding: 0.2rem 0.5rem;
  border-radius: 4px;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}
.tag-adoption {
  background: #e8f4ff;
  color: #2b7de9;
}
.tag-donation {
  background: #fff3e0;
  color: #e07b2b;
}
.tag-city {
  background: #f0ece4;
  color: #888;
}
.post-body h3 {
  font-size: 1rem;
  color: #1a1a2e;
  margin-bottom: 0.4rem;
}
.post-body p {
  font-size: 0.82rem;
  color: #888;
  line-height: 1.6;
  margin-bottom: 0.75rem;
}
.post-footer {
  display: flex;
  justify-content: space-between;
  font-size: 0.75rem;
  color: #aaa;
}
.loading,
.empty {
  text-align: center;
  padding: 3rem;
  color: #aaa;
  font-size: 0.9rem;
}
.alert-error {
  background: #fff0f0;
  color: #e05252;
  border: 1px solid #fcd4d4;
  padding: 0.8rem 1rem;
  border-radius: 6px;
}
</style>
