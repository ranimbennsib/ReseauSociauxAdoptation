<template>
  <div class="page">
    <NavBar />
    <div class="container">
      <h1>Mes dons</h1>
      <div class="tabs">
        <button :class="{ active: tab === 'sent' }" @click="tab = 'sent'">Faits</button>
        <button :class="{ active: tab === 'received' }" @click="tab = 'received'">Reçus</button>
      </div>
      <div v-if="loading" class="loading">Chargement...</div>
      <div v-else-if="donations.length === 0" class="empty">Aucun don.</div>
      <div v-else class="donations-list">
        <div v-for="d in donations" :key="d.id" class="donation-card">
          <div class="donation-info">
            <strong>{{ d.post?.title }}</strong>
            <span class="donation-amount">{{ d.amount }} TND</span>
          </div>
          <div class="donation-meta">
            <span v-if="tab === 'sent'">À: {{ d.owner?.name }}</span>
            <span v-else>De: {{ d.user?.name }} (****{{ d.donorCardLastFour }})</span>
            <span>{{ formatDate(d.createdAt) }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { donationsAPI } from '../services/api'
import NavBar from '../components/NavBar.vue'

const tab = ref('sent')
const donations = ref([])
const loading = ref(false)

const fetchDonations = async () => {
  loading.value = true
  try {
    const res =
      tab.value === 'sent' ? await donationsAPI.getMyDonations() : await donationsAPI.getReceived()
    donations.value = res.data
  } finally {
    loading.value = false
  }
}

const formatDate = (date) => (date ? new Date(date).toLocaleDateString('fr-FR') : '')

watch(tab, fetchDonations)
onMounted(fetchDonations)
</script>

<style scoped>
.page {
  min-height: 100vh;
  background: #fafaf8;
}
.container {
  max-width: 700px;
  margin: 0 auto;
  padding: 2rem 2.5rem;
}
h1 {
  font-family: 'Playfair Display', serif;
  font-size: 1.8rem;
  color: #1a1a2e;
  margin-bottom: 1.5rem;
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
.donations-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}
.donation-card {
  background: #fff;
  border: 1px solid #ede9e0;
  border-radius: 8px;
  padding: 1rem 1.25rem;
}
.donation-info {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.4rem;
}
.donation-info strong {
  font-size: 0.95rem;
  color: #1a1a2e;
}
.donation-amount {
  font-size: 1rem;
  font-weight: 700;
  color: #c8a84b;
}
.donation-meta {
  display: flex;
  justify-content: space-between;
  font-size: 0.78rem;
  color: #aaa;
}
.loading,
.empty {
  text-align: center;
  padding: 3rem;
  color: #aaa;
}
</style>
