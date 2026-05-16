<template>
  <div class="page">
    <NavBar />
    <div class="container">
      <h1>Demandes d'adoption</h1>
      <div class="tabs">
        <button :class="{ active: tab === 'received' }" @click="tab = 'received'">Reçues</button>
        <button :class="{ active: tab === 'sent' }" @click="tab = 'sent'">Envoyées</button>
      </div>

      <div v-if="loading" class="loading">Chargement...</div>
      <div v-else-if="requests.length === 0" class="empty">Aucune demande.</div>
      <div v-else class="requests-list">
        <div v-for="req in requests" :key="req.id" class="request-card">
          <div class="req-header">
            <span class="req-status" :class="`status-${req.status}`">{{ req.status }}</span>
            <strong>{{ req.post?.title }}</strong>
          </div>
          <div class="req-body">
            <p v-if="tab === 'received'">
              De: <strong>{{ req.requester?.name }}</strong>
            </p>
            <p v-else>
              Pour: <strong>{{ req.post?.user?.name }}</strong>
            </p>
            <p>
              Logement: {{ req.housingType }} · Jardin: {{ req.hasGarden ? 'Oui' : 'Non' }} ·
              Enfants: {{ req.hasChildren ? 'Oui' : 'Non' }}
            </p>
            <p v-if="req.message" class="req-message">{{ req.message }}</p>
          </div>
          <div v-if="tab === 'received' && req.status === 'pending'" class="req-actions">
            <button class="btn-accept" @click="accept(req.id)">✓ Accepter</button>
            <button class="btn-reject" @click="reject(req.id)">✗ Refuser</button>
          </div>
          <div v-if="tab === 'sent' && req.status === 'pending'" class="req-actions">
            <button class="btn-cancel" @click="cancel(req.id)">Annuler</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { adoptionAPI } from '../services/api'
import NavBar from '../components/NavBar.vue'

const tab = ref('received')
const requests = ref([])
const loading = ref(false)

const fetchRequests = async () => {
  loading.value = true
  try {
    const res =
      tab.value === 'received' ? await adoptionAPI.getReceived() : await adoptionAPI.getSent()
    requests.value = res.data
  } finally {
    loading.value = false
  }
}

const accept = async (id) => {
  await adoptionAPI.accept(id)
  fetchRequests()
}
const reject = async (id) => {
  await adoptionAPI.reject(id)
  fetchRequests()
}
const cancel = async (id) => {
  await adoptionAPI.cancel(id)
  fetchRequests()
}

watch(tab, fetchRequests)
onMounted(fetchRequests)
</script>

<style scoped>
.page {
  min-height: 100vh;
  background: #fafaf8;
}
.container {
  max-width: 800px;
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
.requests-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}
.request-card {
  background: #fff;
  border: 1px solid #ede9e0;
  border-radius: 8px;
  padding: 1.25rem;
}
.req-header {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 0.75rem;
}
.req-status {
  font-size: 0.65rem;
  font-weight: 600;
  padding: 0.2rem 0.5rem;
  border-radius: 4px;
  text-transform: uppercase;
}
.status-pending {
  background: #fff8e0;
  color: #c8a84b;
}
.status-accepted {
  background: #e8faf0;
  color: #3a9e68;
}
.status-rejected {
  background: #fff0f0;
  color: #e05252;
}
.status-cancelled {
  background: #f0ece4;
  color: #888;
}
.req-body p {
  font-size: 0.83rem;
  color: #666;
  margin: 0.2rem 0;
}
.req-message {
  font-style: italic;
  color: #888;
  margin-top: 0.4rem !important;
}
.req-actions {
  display: flex;
  gap: 0.5rem;
  margin-top: 0.75rem;
}
.btn-accept {
  padding: 0.4rem 1rem;
  background: #3a9e68;
  color: #fff;
  border: none;
  border-radius: 5px;
  font-size: 0.8rem;
  cursor: pointer;
}
.btn-reject {
  padding: 0.4rem 1rem;
  background: #e05252;
  color: #fff;
  border: none;
  border-radius: 5px;
  font-size: 0.8rem;
  cursor: pointer;
}
.btn-cancel {
  padding: 0.4rem 1rem;
  background: #f5f0e8;
  color: #888;
  border: none;
  border-radius: 5px;
  font-size: 0.8rem;
  cursor: pointer;
}
.loading,
.empty {
  text-align: center;
  padding: 3rem;
  color: #aaa;
}
</style>
