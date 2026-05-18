<template>
  <div class="page">
    <NavBar />
    <div class="container">
      <div class="page-header">
        <h1>Tous les animaux</h1>
      </div>

      <!-- 🔍 Barre de recherche et filtres -->
      <div class="search-section">
        <div class="search-bar">
          <svg class="search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="10" cy="10" r="7" />
            <line x1="15" y1="15" x2="21" y2="21" />
          </svg>
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Rechercher par titre, description, catégorie..."
            @input="onSearchInput"
          />
          <button v-if="searchQuery" class="clear-search" @click="clearSearch">✕</button>
        </div>

        <div class="filters-row">
          <div class="filter-group">
            <label>Type</label>
            <div class="filter-buttons">
              <button 
                :class="{ active: filters.type === 'all' }" 
                @click="filters.type = 'all'"
              >Tous</button>
              <button 
                :class="{ active: filters.type === 'adoption' }" 
                @click="filters.type = 'adoption'"
              >🐾 Adoption</button>
              <button 
                :class="{ active: filters.type === 'donation' }" 
                @click="filters.type = 'donation'"
              >💰 Donation</button>
            </div>
          </div>

          <div class="filter-group">
            <label>Ville</label>
            <select v-model="filters.city">
              <option value="">Toutes les villes</option>
              <option v-for="city in cities" :key="city" :value="city">
                {{ city.replace('_', ' ') }}
              </option>
            </select>
          </div>

          <div class="filter-group">
            <label>Catégorie</label>
            <select v-model="filters.category">
              <option value="">Toutes les catégories</option>
              <option v-for="cat in availableCategories" :key="cat" :value="cat">
                {{ cat }}
              </option>
            </select>
          </div>

         

          <div class="filter-group">
            <label>Trier par</label>
            <select v-model="filters.sort">
              <option value="latest">Plus récent</option>
              <option value="oldest">Plus ancien</option>
              <option value="title_asc">Titre A→Z</option>
              <option value="title_desc">Titre Z→A</option>
            </select>
          </div>
        </div>

        <div class="results-info">
          <span>{{ filteredPosts.length }} annonce(s) trouvée(s)</span>
          <button v-if="hasActiveFilters" class="reset-filters" @click="resetFilters">
            Réinitialiser les filtres
          </button>
        </div>
      </div>

      <div v-if="loading" class="loading">Chargement...</div>
      <div v-else-if="error" class="alert alert-error">{{ error }}</div>
      <div v-else class="posts-grid">
        <div
          v-for="post in paginatedPosts"
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
              <span v-if="post.is_adoption" class="tag tag-adoption">Adoption</span>
              <span v-if="post.is_donation" class="tag tag-donation">Don</span>
              <span class="tag tag-city">{{ post.city?.replace('_', ' ') }}</span>
              <span v-if="post.status !== 'available'" class="tag" :class="`tag-${post.status}`">
                {{ post.status }}
              </span>
            </div>
            <h3>{{ post.title }}</h3>
            <p>{{ truncateText(post.description, 80) }}</p>
            <div class="post-footer">
              <span class="post-category">{{ post.category }}</span>
              <span class="post-date">{{ formatDate(post.created_at) }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="filteredPosts.length > 0" class="pagination">
        <button 
          @click="currentPage--" 
          :disabled="currentPage === 1"
          class="page-btn"
        >← Précédent</button>
        <span class="page-info">Page {{ currentPage }} sur {{ totalPages }}</span>
        <button 
          @click="currentPage++" 
          :disabled="currentPage === totalPages"
          class="page-btn"
        >Suivant →</button>
      </div>

      <div v-if="!loading && filteredPosts.length === 0" class="empty">
        Aucun animal ne correspond à vos critères.
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { postsAPI } from '../services/api'
import NavBar from '../components/NavBar.vue'

const router = useRouter()
const posts = ref([])
const loading = ref(false)
const error = ref('')

// État des filtres
const searchQuery = ref('')
const searchDebounce = ref(null)

const filters = ref({
  type: 'all',
  city: '',
  category: '',
  sort: 'latest'
})

// Pagination
const currentPage = ref(1)
const postsPerPage = 12

// Liste des villes
const cities = [
  'Tunis', 'Sfax', 'Sousse', 'Kairouan', 'Bizerte', 'Gabes',
  'Ariana', 'Gafsa', 'Monastir', 'Ben_Arous', 'Kasserine',
  'Medenine', 'Nabeul', 'Tataouine', 'Beja', 'Jendouba',
  'El_Kef', 'Mahdia', 'Sidi_Bouzid', 'Siliana', 'Zaghouan',
  'Tozeur', 'Manouba', 'Kebili'
]

// Catégories disponibles (extraites des posts)
const availableCategories = computed(() => {
  const cats = new Set()
  posts.value.forEach(post => {
    if (post.category) cats.add(post.category)
  })
  return Array.from(cats).sort()
})

// Vérifie si des filtres sont actifs
const hasActiveFilters = computed(() => {
  return searchQuery.value !== '' ||
    filters.value.city !== '' ||
    filters.value.category !== '' ||
    filters.value.status !== '' ||
    filters.value.type !== 'all'
})

// Filtrage et recherche
const filteredPosts = computed(() => {
  let result = [...posts.value]

  // 🔍 Recherche textuelle
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    result = result.filter(post => 
      post.title?.toLowerCase().includes(query) ||
      post.description?.toLowerCase().includes(query) ||
      post.category?.toLowerCase().includes(query)
    )
  }

  // Filtre par type
  if (filters.value.type === 'adoption') {
    result = result.filter(post => post.is_adoption === true || post.is_adoption === 1)
  } else if (filters.value.type === 'donation') {
    result = result.filter(post => post.is_donation === true || post.is_donation === 1)
  }

  // Filtre par ville
  if (filters.value.city) {
    result = result.filter(post => post.city === filters.value.city)
  }

  // Filtre par catégorie
  if (filters.value.category) {
    result = result.filter(post => post.category === filters.value.category)
  }

  // Filtre par statut
  if (filters.value.status) {
    result = result.filter(post => post.status === filters.value.status)
  }

  // Tri
  switch (filters.value.sort) {
    case 'latest':
      result.sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
      break
    case 'oldest':
      result.sort((a, b) => new Date(a.created_at) - new Date(b.created_at))
      break
    case 'title_asc':
      result.sort((a, b) => (a.title || '').localeCompare(b.title || ''))
      break
    case 'title_desc':
      result.sort((a, b) => (b.title || '').localeCompare(a.title || ''))
      break
  }

  return result
})

// Posts paginés
const paginatedPosts = computed(() => {
  const start = (currentPage.value - 1) * postsPerPage
  const end = start + postsPerPage
  return filteredPosts.value.slice(start, end)
})

// Nombre total de pages
const totalPages = computed(() => {
  return Math.ceil(filteredPosts.value.length / postsPerPage) || 1
})

// Réinitialiser la page quand les filtres changent
watch([filteredPosts, filters, searchQuery], () => {
  if (currentPage.value > totalPages.value) {
    currentPage.value = 1
  }
})

// Debounce pour la recherche
const onSearchInput = () => {
  if (searchDebounce.value) clearTimeout(searchDebounce.value)
  searchDebounce.value = setTimeout(() => {
    currentPage.value = 1
  }, 300)
}

const clearSearch = () => {
  searchQuery.value = ''
  currentPage.value = 1
}

const resetFilters = () => {
  searchQuery.value = ''
  filters.value = {
    type: 'all',
    city: '',
    category: '',
    status: '',
    sort: 'latest'
  }
  currentPage.value = 1
}

const truncateText = (text, maxLength) => {
  if (!text) return ''
  return text.length > maxLength ? text.substring(0, maxLength) + '...' : text
}

const formatDate = (date) => date ? new Date(date).toLocaleDateString('fr-FR') : ''

// Charger les posts depuis l'API
const fetchPosts = async () => {
  loading.value = true
  error.value = ''
  try {
    const res = await postsAPI.getAll()
    posts.value = res.data
  } catch (e) {
    error.value = 'Erreur lors du chargement des posts'
    console.error(e)
  } finally {
    loading.value = false
  }
}

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
  margin-bottom: 2rem;
}
.page-header h1 {
  font-family: 'Playfair Display', serif;
  font-size: 2rem;
  color: #1a1a2e;
}

/* Section recherche */
.search-section {
  background: #fff;
  border: 1px solid #ede9e0;
  border-radius: 12px;
  padding: 1.5rem;
  margin-bottom: 2rem;
}
.search-bar {
  position: relative;
  margin-bottom: 1.5rem;
}
.search-icon {
  position: absolute;
  left: 1rem;
  top: 50%;
  transform: translateY(-50%);
  color: #aaa;
}
.search-bar input {
  width: 100%;
  padding: 0.8rem 2.5rem 0.8rem 2.8rem;
  border: 1.5px solid #e8e3d8;
  border-radius: 10px;
  font-size: 0.9rem;
  font-family: inherit;
  outline: none;
  transition: border-color 0.2s;
}
.search-bar input:focus {
  border-color: #c8a84b;
}
.clear-search {
  position: absolute;
  right: 1rem;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  color: #aaa;
  cursor: pointer;
  font-size: 1rem;
}
.filters-row {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
  margin-bottom: 1rem;
}
.filter-group {
  flex: 1;
  min-width: 120px;
}
.filter-group label {
  display: block;
  font-size: 0.7rem;
  font-weight: 600;
  color: #888;
  margin-bottom: 0.3rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}
.filter-group select {
  width: 100%;
  padding: 0.5rem 0.8rem;
  border: 1.5px solid #e8e3d8;
  border-radius: 8px;
  font-size: 0.85rem;
  font-family: inherit;
  background: #fff;
  cursor: pointer;
}
.filter-buttons {
  display: flex;
  gap: 0.3rem;
}
.filter-buttons button {
  padding: 0.5rem 0.8rem;
  border: 1.5px solid #e8e3d8;
  border-radius: 8px;
  background: #fff;
  font-size: 0.75rem;
  cursor: pointer;
  transition: all 0.2s;
}
.filter-buttons button.active {
  background: #1a1a2e;
  color: #fff;
  border-color: #1a1a2e;
}
.results-info {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-top: 1rem;
  border-top: 1px solid #f0ece4;
  font-size: 0.8rem;
  color: #888;
}
.reset-filters {
  background: none;
  border: none;
  color: #c8a84b;
  cursor: pointer;
  font-size: 0.8rem;
}

/* Grille des posts */
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
  transition: transform 0.2s, box-shadow 0.2s;
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
.tag-closed, .tag-adopted, .tag-removed {
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

/* Pagination */
.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 1rem;
  margin-top: 2rem;
  padding-top: 1rem;
  border-top: 1px solid #f0ece4;
}
.page-btn {
  padding: 0.5rem 1rem;
  background: #fff;
  border: 1px solid #e8e3d8;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s;
}
.page-btn:hover:not(:disabled) {
  background: #1a1a2e;
  color: #fff;
  border-color: #1a1a2e;
}
.page-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
.page-info {
  font-size: 0.85rem;
  color: #888;
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
  margin-bottom: 1rem;
}
</style>