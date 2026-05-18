<template>
  <div class="page">
    <NavBar />
    <div class="container">
      <button class="back-btn" @click="router.back()">← Retour</button>

      <div class="form-header">
        <h1>Modifier mon annonce</h1>
        <p>Modifiez les informations de votre annonce</p>
      </div>

      <form @submit.prevent="handleSubmit" class="edit-form">
        <!-- Image -->
        <div class="form-group">
          <label>Image actuelle</label>
          <div class="current-image" v-if="form.image">
            <img :src="form.image" alt="Image actuelle" />
            <button type="button" class="btn-change-image" @click="showImageModal = true">
              Changer l'image
            </button>
          </div>
        </div>

        <!-- Titre -->
        <div class="form-group">
          <label>Titre *</label>
          <input v-model="form.title" type="text" required />
        </div>

        <!-- Description -->
        <div class="form-group">
          <label>Description *</label>
          <textarea v-model="form.description" rows="5" required></textarea>
        </div>

        <!-- Catégorie -->
        <div class="form-row">
          <div class="form-group">
            <label>Catégorie *</label>
            <input v-model="form.category" placeholder="Chat, Chien, Lapin..." required />
          </div>
          <div class="form-group">
            <label>Ville *</label>
            <select v-model="form.city" required>
              <option value="">Sélectionner</option>
              <option v-for="city in cities" :key="city" :value="city">
                {{ city.replace('_', ' ') }}
              </option>
            </select>
          </div>
        </div>

        <!-- Localisation -->
        <div class="form-group">
          <label>Localisation détaillée</label>
          <input v-model="form.localisation_detail" placeholder="Quartier, rue..." />
        </div>

        <!-- Options (Adoption / Donation) -->
        <div class="form-group">
          <label>Options</label>
          <div class="options-row">
            <label class="option-card" :class="{ active: form.is_adoption }">
              <input type="checkbox" v-model="form.is_adoption" />
              <span>🐾 Adoption</span>
            </label>
            <label class="option-card" :class="{ active: form.is_donation }">
              <input type="checkbox" v-model="form.is_donation" />
              <span>💰 Dons financiers</span>
            </label>
          </div>
        </div>

        <!-- Donation fields -->
        <div v-if="form.is_donation" class="form-group donation-fields">
          <label>Informations bancaires</label>
          <div class="donation-row">
            <input v-model="form.card_number" placeholder="Numéro de carte" maxlength="16" />
            <input v-model="form.card_holder_name" placeholder="Nom sur la carte" />
          </div>
        </div>

        <!-- Messages -->
        <div v-if="errorMessage" class="alert error">{{ errorMessage }}</div>
        <div v-if="successMessage" class="alert success">{{ successMessage }}</div>

        <!-- Actions -->
        <div class="form-actions">
          <button type="button" class="btn-cancel" @click="router.back()">Annuler</button>
          <button type="submit" class="btn-submit" :disabled="loading">
            {{ loading ? 'Enregistrement...' : 'Enregistrer les modifications' }}
          </button>
        </div>
      </form>
    </div>

    <!-- Modal pour changer l'image -->
    <Teleport to="body">
      <Transition name="modal-fade">
        <div v-if="showImageModal" class="modal-overlay" @click.self="showImageModal = false">
          <div class="modal">
            <h3>Changer l'image</h3>
            <div class="drop-zone" @dragover.prevent @drop.prevent="handleDrop" @click="triggerFileInput">
              <img v-if="newImagePreview" :src="newImagePreview" class="preview-img" />
              <div v-else class="drop-placeholder">
                <span>📷</span>
                <p>Glissez une nouvelle image ou cliquez</p>
              </div>
            </div>
            <input ref="fileInput" type="file" accept="image/*" class="hidden" @change="handleFileChange" />
            <div class="modal-actions">
              <button class="btn-secondary" @click="showImageModal = false">Annuler</button>
              <button class="btn-primary" @click="updateImage" :disabled="!newImage">Valider</button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { postsAPI } from '../services/api'
import { useAuthStore } from '../stores/authStore'
import NavBar from '../components/NavBar.vue'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

const loading = ref(false)
const errorMessage = ref('')
const successMessage = ref('')
const postId = ref(route.params.id)

// Image modal
const showImageModal = ref(false)
const fileInput = ref(null)
const newImage = ref(null)
const newImagePreview = ref('')

const cities = [
  'Tunis', 'Sfax', 'Sousse', 'Kairouan', 'Bizerte', 'Gabes',
  'Ariana', 'Gafsa', 'Monastir', 'Ben_Arous', 'Kasserine',
  'Medenine', 'Nabeul', 'Tataouine', 'Beja', 'Jendouba',
  'El_Kef', 'Mahdia', 'Sidi_Bouzid', 'Siliana', 'Zaghouan',
  'Tozeur', 'Manouba', 'Kebili',
]

const form = reactive({
  title: '',
  description: '',
  category: '',
  city: '',
  localisation_detail: '',
  image: '',
  is_adoption: false,
  is_donation: false,
  card_number: '',
  card_holder_name: '',
})

// Charger les données du post
onMounted(async () => {
  try {
    const response = await postsAPI.getById(postId.value)
    const post = response.data
    
    // Vérifier si l'utilisateur est le propriétaire
    if (post.user?.id !== authStore.user?.id) {
      errorMessage.value = 'Vous n\'êtes pas autorisé à modifier ce post.'
      setTimeout(() => router.push('/my-posts'), 2000)
      return
    }
    
    // Remplir le formulaire
    form.title = post.title
    form.description = post.description
    form.category = post.category
    form.city = post.city
    form.localisation_detail = post.localisation_detail || ''
    form.image = post.image
    form.is_adoption = post.is_adoption
    form.is_donation = post.is_donation
    form.card_number = post.card_number || ''
    form.card_holder_name = post.card_holder_name || ''
  } catch (error) {
    errorMessage.value = 'Erreur lors du chargement du post'
    console.error(error)
  }
})

// Gestion du fichier
const triggerFileInput = () => fileInput.value?.click()

const handleFileChange = (e) => {
  const file = e.target.files[0]
  if (file) {
    newImage.value = file
    const reader = new FileReader()
    reader.onload = (event) => {
      newImagePreview.value = event.target.result
    }
    reader.readAsDataURL(file)
  }
}

const handleDrop = (e) => {
  const file = e.dataTransfer.files[0]
  if (file) {
    newImage.value = file
    const reader = new FileReader()
    reader.onload = (event) => {
      newImagePreview.value = event.target.result
    }
    reader.readAsDataURL(file)
  }
}

const updateImage = async () => {
  if (!newImage.value) return
  
  const reader = new FileReader()
  reader.onload = async (event) => {
    form.image = event.target.result
    showImageModal.value = false
    successMessage.value = 'Image mise à jour, n\'oubliez pas d\'enregistrer'
    setTimeout(() => successMessage.value = '', 3000)
  }
  reader.readAsDataURL(newImage.value)
}

// Soumettre le formulaire
const handleSubmit = async () => {
  errorMessage.value = ''
  
  if (!form.is_adoption && !form.is_donation) {
    errorMessage.value = 'Le post doit être au moins une adoption ou un don.'
    return
  }
  
  loading.value = true
  
  try {
    const dataToSend = {
      title: form.title,
      description: form.description,
      category: form.category,
      city: form.city,
      localisation_detail: form.localisation_detail,
      image: form.image,
      is_adoption: form.is_adoption ? 1 : 0,
      is_donation: form.is_donation ? 1 : 0,
      card_number: form.card_number || null,
      card_holder_name: form.card_holder_name || null,
    }
    
    await postsAPI.update(postId.value, dataToSend)
    successMessage.value = 'Post modifié avec succès !'
    setTimeout(() => router.push('/my-posts'), 1500)
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Erreur lors de la modification'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.page {
  min-height: 100vh;
  background: #fafaf8;
  font-family: 'DM Sans', sans-serif;
}
.container {
  max-width: 700px;
  margin: 0 auto;
  padding: 2rem;
}
.back-btn {
  background: none;
  border: none;
  color: #888;
  cursor: pointer;
  font-size: 0.85rem;
  margin-bottom: 1.5rem;
}
.form-header {
  margin-bottom: 2rem;
}
.form-header h1 {
  font-family: 'Playfair Display', serif;
  font-size: 1.8rem;
  color: #1a1a2e;
  margin-bottom: 0.5rem;
}
.form-header p {
  color: #888;
}
.edit-form {
  background: #fff;
  border: 1px solid #ede9e0;
  border-radius: 12px;
  padding: 2rem;
}
.form-group {
  margin-bottom: 1.5rem;
}
.form-group label {
  display: block;
  font-size: 0.8rem;
  font-weight: 600;
  color: #1a1a2e;
  margin-bottom: 0.5rem;
}
.form-group input,
.form-group textarea,
.form-group select {
  width: 100%;
  padding: 0.7rem 1rem;
  border: 1.5px solid #e8e3d8;
  border-radius: 8px;
  font-size: 0.85rem;
  font-family: inherit;
  outline: none;
}
.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
  border-color: #c8a84b;
}
.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}
.options-row {
  display: flex;
  gap: 1rem;
}
.option-card {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.8rem;
  border: 1.5px solid #e8e3d8;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s;
}
.option-card.active {
  border-color: #1a1a2e;
  background: #f5f0e8;
}
.option-card input {
  display: none;
}
.donation-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}
.current-image {
  display: flex;
  align-items: center;
  gap: 1rem;
}
.current-image img {
  width: 80px;
  height: 80px;
  object-fit: cover;
  border-radius: 8px;
}
.btn-change-image {
  padding: 0.5rem 1rem;
  background: #f5f0e8;
  border: none;
  border-radius: 6px;
  cursor: pointer;
}
.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  margin-top: 2rem;
  padding-top: 1rem;
  border-top: 1px solid #ede9e0;
}
.btn-cancel {
  padding: 0.7rem 1.5rem;
  background: #f5f0e8;
  border: none;
  border-radius: 8px;
  cursor: pointer;
}
.btn-submit {
  padding: 0.7rem 1.5rem;
  background: #1a1a2e;
  color: #fff;
  border: none;
  border-radius: 8px;
  cursor: pointer;
}
.btn-submit:hover:not(:disabled) {
  background: #c8a84b;
}
.alert {
  padding: 0.8rem;
  border-radius: 8px;
  margin-bottom: 1rem;
}
.alert.error {
  background: #fff0f0;
  color: #e05252;
  border: 1px solid #fcd4d4;
}
.alert.success {
  background: #e8faf0;
  color: #3a9e68;
  border: 1px solid #c3e6d4;
}
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}
.modal {
  background: #fff;
  border-radius: 12px;
  padding: 2rem;
  width: 90%;
  max-width: 500px;
}
.modal h3 {
  margin-bottom: 1rem;
}
.drop-zone {
  border: 2px dashed #ccc;
  border-radius: 8px;
  padding: 2rem;
  text-align: center;
  cursor: pointer;
  margin-bottom: 1rem;
}
.preview-img {
  max-width: 100%;
  max-height: 200px;
  object-fit: cover;
}
.hidden {
  display: none;
}
.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
}
.btn-primary, .btn-secondary {
  padding: 0.5rem 1rem;
  border-radius: 6px;
  cursor: pointer;
}
.btn-primary {
  background: #1a1a2e;
  color: #fff;
  border: none;
}
.btn-secondary {
  background: #f5f0e8;
  border: none;
}
.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: opacity 0.2s;
}
.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
}
</style>