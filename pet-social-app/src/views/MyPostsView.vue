<template>
  <div class="page">
    <NavBar />
    <div class="container">
      <div class="page-header">
        <h1>Mes annonces</h1>
        <router-link to="/posts/create" class="btn-primary">+ Nouvelle annonce</router-link>
      </div>

      <div v-if="loading" class="loading">Chargement...</div>
      <div v-else-if="posts.length === 0" class="empty">
        Vous n'avez pas encore publié d'annonce.
      </div>
      <div v-else class="posts-list">
        <div v-for="post in posts" :key="post.id" class="post-row">
          <div class="post-row-info">
            <span class="post-status" :class="`status-${post.status}`">{{ post.status }}</span>
            <h3 @click="router.push(`/posts/${post.id}`)">{{ post.title }}</h3>
            <span class="post-city">{{ post.city?.replace('_', ' ') }} · {{ post.category }}</span>
          </div>
          <div class="post-row-actions">
            <button
              v-if="post.status === 'available'"
              class="btn-outline"
              @click="router.push(`/posts/${post.id}`)"
            >
              Voir
            </button>
            <button
              v-if="post.status === 'available'"
              class="btn-ghost"
              @click="closePost(post.id)"
            >
              Fermer
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { postsAPI } from '../services/api'
import NavBar from '../components/NavBar.vue'

const router = useRouter()
const posts = ref([])
const loading = ref(true)

onMounted(async () => {
  try {
    const res = await postsAPI.getMyPosts()
    posts.value = res.data
  } finally {
    loading.value = false
  }
})

const closePost = async (id) => {
  if (!confirm('Fermer cette annonce ?')) return
  try {
    await postsAPI.close(id)
    const post = posts.value.find((p) => p.id === id)
    if (post) post.status = 'closed'
  } catch (e) {
    alert(e.response?.data?.message || 'Erreur')
  }
}
</script>

<style scoped>
.page {
  min-height: 100vh;
  background: #fafaf8;
}
.container {
  max-width: 900px;
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
  font-size: 1.8rem;
  color: #1a1a2e;
}
.btn-primary {
  padding: 0.6rem 1.4rem;
  background: #1a1a2e;
  color: #fff;
  border: none;
  border-radius: 6px;
  font-size: 0.82rem;
  cursor: pointer;
  text-decoration: none;
  transition: background 0.2s;
}
.btn-primary:hover {
  background: #c8a84b;
}
.posts-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}
.post-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: #fff;
  border: 1px solid #ede9e0;
  border-radius: 8px;
  padding: 1rem 1.25rem;
}
.post-row-info {
  display: flex;
  align-items: center;
  gap: 1rem;
}
.post-status {
  font-size: 0.65rem;
  font-weight: 600;
  padding: 0.2rem 0.5rem;
  border-radius: 4px;
  text-transform: uppercase;
}
.status-available {
  background: #e8faf0;
  color: #3a9e68;
}
.status-adopted {
  background: #e8f4ff;
  color: #2b7de9;
}
.status-closed {
  background: #f0ece4;
  color: #888;
}
.status-removed {
  background: #fff0f0;
  color: #e05252;
}
.post-row-info h3 {
  font-size: 0.95rem;
  color: #1a1a2e;
  cursor: pointer;
}
.post-row-info h3:hover {
  color: #c8a84b;
}
.post-city {
  font-size: 0.78rem;
  color: #aaa;
}
.post-row-actions {
  display: flex;
  gap: 0.5rem;
}
.btn-outline {
  padding: 0.4rem 0.9rem;
  background: transparent;
  border: 1px solid #1a1a2e;
  color: #1a1a2e;
  border-radius: 5px;
  font-size: 0.78rem;
  cursor: pointer;
}
.btn-ghost {
  padding: 0.4rem 0.9rem;
  background: transparent;
  border: 1px solid #e5e0d5;
  color: #e05252;
  border-radius: 5px;
  font-size: 0.78rem;
  cursor: pointer;
}
.loading,
.empty {
  text-align: center;
  padding: 3rem;
  color: #aaa;
}
</style>
