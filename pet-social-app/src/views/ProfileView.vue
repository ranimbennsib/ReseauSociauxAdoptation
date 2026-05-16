<template>
  <div class="page">
    <NavBar />
    <div class="container">
      <div class="profile-card">
        <div class="avatar">{{ authStore.userName.charAt(0).toUpperCase() }}</div>
        <h2>{{ authStore.userName }}</h2>
        <p class="email">{{ authStore.userEmail }}</p>
        <span class="role-badge" :class="`role-${authStore.userRole}`">{{
          authStore.userRole
        }}</span>

        <div class="profile-links">
          <router-link to="/my-posts" class="profile-link">Mes annonces</router-link>
          <router-link to="/adoption-requests" class="profile-link">Mes demandes</router-link>
          <router-link to="/donations" class="profile-link">Mes dons</router-link>
          <router-link v-if="authStore.isAdmin" to="/admin" class="profile-link"
            >⚙️ Administration</router-link
          >
        </div>

        <button class="btn-logout" @click="handleLogout">Se déconnecter</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/authStore'
import NavBar from '../components/NavBar.vue'

const router = useRouter()
const authStore = useAuthStore()

const handleLogout = () => {
  authStore.logout()
  router.push('/')
}
</script>

<style scoped>
.page {
  min-height: 100vh;
  background: #fafaf8;
}
.container {
  max-width: 480px;
  margin: 0 auto;
  padding: 3rem 2rem;
}
.profile-card {
  background: #fff;
  border: 1px solid #ede9e0;
  border-radius: 12px;
  padding: 2.5rem;
  text-align: center;
}
.avatar {
  width: 72px;
  height: 72px;
  background: #c8a84b;
  color: #fff;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.8rem;
  font-weight: 700;
  margin: 0 auto 1rem;
}
.profile-card h2 {
  font-family: 'Playfair Display', serif;
  font-size: 1.5rem;
  color: #1a1a2e;
  margin-bottom: 0.25rem;
}
.email {
  font-size: 0.85rem;
  color: #aaa;
  margin-bottom: 0.75rem;
}
.role-badge {
  font-size: 0.65rem;
  font-weight: 600;
  padding: 0.25rem 0.7rem;
  border-radius: 20px;
  text-transform: uppercase;
  display: inline-block;
  margin-bottom: 2rem;
}
.role-user {
  background: #e8f4ff;
  color: #2b7de9;
}
.role-admin {
  background: #fff3e0;
  color: #e07b2b;
}
.profile-links {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  margin-bottom: 1.5rem;
}
.profile-link {
  display: block;
  padding: 0.75rem 1rem;
  background: #fafaf8;
  border: 1px solid #ede9e0;
  border-radius: 7px;
  text-decoration: none;
  color: #1a1a2e;
  font-size: 0.85rem;
  text-align: left;
  transition: background 0.2s;
}
.profile-link:hover {
  background: #fdf5e8;
}
.btn-logout {
  width: 100%;
  padding: 0.7rem;
  background: #fff0f0;
  color: #e05252;
  border: 1px solid #fcd4d4;
  border-radius: 7px;
  font-size: 0.85rem;
  cursor: pointer;
  transition: background 0.2s;
}
.btn-logout:hover {
  background: #e05252;
  color: #fff;
}
</style>
