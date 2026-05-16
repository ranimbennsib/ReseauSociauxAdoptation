<template>
  <div class="page">
    <NavBar />


    <div class="detail-wrapper" v-if="post">


      <!-- ══════════════════ LEFT PANEL ══════════════════ -->
      <aside class="left-panel">


        <!-- Paw print decoration -->
        <div class="paw-prints" aria-hidden="true">
          <svg v-for="n in 9" :key="n" :class="`paw lp${n}`" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
            <g fill="currentColor">
              <ellipse cx="50" cy="72" rx="22" ry="18"/>
              <ellipse cx="22" cy="48" rx="10" ry="13" transform="rotate(-20 22 48)"/>
              <ellipse cx="78" cy="48" rx="10" ry="13" transform="rotate(20 78 48)"/>
              <ellipse cx="34" cy="30" rx="9" ry="11" transform="rotate(-10 34 30)"/>
              <ellipse cx="66" cy="30" rx="9" ry="11" transform="rotate(10 66 30)"/>
            </g>
          </svg>
        </div>


        <!-- Back button -->
        <button class="back-btn" @click="router.back()">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M19 12H5M12 5l-7 7 7 7"/>
          </svg>
          Retour
        </button>


        <!-- Hero photo -->
        <div class="hero-photo-wrap">
          <img v-if="post.image" :src="post.image" :alt="post.title" class="hero-photo"/>
          <div v-else class="hero-photo-placeholder">🐾</div>
          <div class="hero-overlay"></div>


          <!-- Status chip -->
          <div class="status-chip" :class="post.status === 'available' ? 'sc-available' : 'sc-adopted'">
            {{ post.status === 'available' ? '✨ Disponible' : '🏠 Adopté' }}
          </div>
        </div>


        <!-- Type pills -->
        <div class="type-pills">
          <span v-if="post.is_adoption" class="tpill tpill-blue">🤍 Adoption</span>
          <span v-if="post.is_donation" class="tpill tpill-gold">🧡 Don accepté</span>
        </div>


        <!-- Animal name + category -->
        <div class="animal-identity">
          <div class="animal-name">{{ post.title }}</div>
          <div class="animal-cat">{{ post.category }}</div>
        </div>


        <!-- Metadata rows -->
        <div class="meta-block">
          <div class="meta-row">
            <span class="mk">🏙 Ville</span>
            <span class="mv">{{ post.city?.replace('_', ' ') }}</span>
          </div>
          <div class="meta-row">
            <span class="mk">📍 Localisation</span>
            <span class="mv">{{ post.localisation_detail || '—' }}</span>
          </div>
          <div class="meta-row">
            <span class="mk">👤 Publié par</span>
            <span class="mv">{{ post.user?.name }}</span>
          </div>
          <div class="meta-row">
            <span class="mk">📅 Date</span>
            <span class="mv">{{ formatDate(post.created_at) }}</span>
          </div>
        </div>


        <!-- Footer tagline -->
        <div class="left-footer">
          <p class="left-tagline">"Offrez-leur une seconde chance de vivre aimés." 🐾</p>
        </div>
      </aside>


      <!-- ══════════════════ RIGHT PANEL ══════════════════ -->
      <main class="right-panel">


        <!-- Decorative paw prints -->
        <div class="paw-prints-right" aria-hidden="true">
          <svg v-for="n in 6" :key="n" :class="`paw rp${n}`" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
            <g fill="currentColor">
              <ellipse cx="50" cy="72" rx="22" ry="18"/>
              <ellipse cx="22" cy="48" rx="10" ry="13" transform="rotate(-20 22 48)"/>
              <ellipse cx="78" cy="48" rx="10" ry="13" transform="rotate(20 78 48)"/>
              <ellipse cx="34" cy="30" rx="9" ry="11" transform="rotate(-10 34 30)"/>
              <ellipse cx="66" cy="30" rx="9" ry="11" transform="rotate(10 66 30)"/>
            </g>
          </svg>
        </div>


        <div class="right-body">


          <!-- ── Heading ── -->
          <div class="post-heading">
            <span class="post-eyebrow">Publication · Animaux</span>
            <h1 class="post-title">{{ post.title }}</h1>
            <div class="wavy-line">
              <svg viewBox="0 0 200 12" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
                <path d="M0,6 C30,0 50,12 80,6 C110,0 130,12 160,6 C175,3 190,9 200,6"
                  stroke="#f5d96b" stroke-width="2.5" fill="none" stroke-linecap="round"/>
              </svg>
            </div>
          </div>


          <!-- ── Description card ── -->
          <div class="desc-card">
            <div class="desc-icon">🐾</div>
            <p class="post-desc">{{ post.description }}</p>
          </div>


          <div class="rule"></div>


          <!-- ── Actions ── -->
          <div v-if="authStore.isAuthenticated && post.user?.id !== authStore.user?.id" class="actions-section">


            <div class="actions-label">Actions disponibles</div>


            <!-- Adoption card -->
            <div v-if="post.is_adoption && post.status === 'available'" class="action-card ac-adopt">
              <div class="ac-icon-wrap">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                  <path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/>
                </svg>
              </div>
              <div class="ac-text">
                <span class="ac-label">Adoption disponible</span>
                <span class="ac-sub">Prêt à accueillir cet animal dans votre foyer ?</span>
              </div>
              <button class="btn-publish" @click="showAdoptionModal = true">Faire une demande →</button>
            </div>


            <!-- Donation card -->
            <div v-if="post.is_donation && post.status === 'available'" class="action-card ac-donate">
              <div class="ac-icon-wrap gold">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                  <line x1="12" y1="1" x2="12" y2="23"/>
                  <path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/>
                </svg>
              </div>
              <div class="ac-text">
                <span class="ac-label">Don financier accepté</span>
                <span class="ac-sub">Un geste peut tout changer pour cet animal.</span>
              </div>
              <button class="btn-draft" @click="showDonationForm = !showDonationForm">
                {{ showDonationForm ? 'Fermer ↑' : 'Faire un don →' }}
              </button>
            </div>


            <!-- Donation inline form -->
            <transition name="slide">
              <div v-if="showDonationForm && post.is_donation && post.status === 'available'" class="donation-form-box">
                <div class="dfbox-head">💳 Informations de paiement</div>
                <div class="donation-grid">
                  <div class="dfield">
                    <label>Numéro de carte</label>
                    <input v-model="donationForm.cardNumber" placeholder="1234 5678 9012 3456" maxlength="16"/>
                  </div>
                  <div class="dfield">
                    <label>Nom sur la carte</label>
                    <input v-model="donationForm.cardHolderName" placeholder="Titulaire"/>
                  </div>
                  <div class="dfield full">
                    <label>Montant (TND)</label>
                    <input v-model.number="donationForm.amount" type="number" placeholder="Ex : 20" min="1"/>
                  </div>
                </div>
                <div class="donation-actions">
                  <button class="btn-draft" @click="showDonationForm = false">Annuler</button>
                  <button class="btn-publish" @click="submitDonation" :disabled="donationLoading">
                    {{ donationLoading ? 'Envoi…' : 'Confirmer le don →' }}
                  </button>
                </div>
                <div v-if="donationMsg" :class="donationMsg.type === 'success' ? 'alert-success' : 'alert-error'">
                  {{ donationMsg.text }}
                </div>
              </div>
            </transition>


            <!-- Report trigger -->
            <button class="report-trigger" @click="showReportModal = true">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                <line x1="12" y1="9" x2="12" y2="13"/>
                <line x1="12" y1="17" x2="12.01" y2="17"/>
              </svg>
              Signaler ce post
            </button>
          </div>


          <div class="rule"></div>


          <!-- ── Comments ── -->
          <div class="comments-section">
            <div class="comments-hd">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/>
              </svg>
              <h3>Commentaires</h3>
            </div>


            <div v-if="authStore.isAuthenticated" class="compose-bar">
              <input v-model="newComment" placeholder="Ajouter un commentaire…" @keyup.enter="submitComment"/>
              <button class="btn-publish sm" @click="submitComment">Envoyer</button>
            </div>


            <div class="comment-list">
              <div v-for="comment in comments" :key="comment.id" class="comment-item">
                <div class="c-avatar">{{ comment.user?.name?.[0]?.toUpperCase() }}</div>
                <div class="c-bubble">
                  <div class="c-top">
                    <span class="c-name">{{ comment.user?.name }}</span>
                    <span class="c-date">{{ formatDate(comment.created_at) }}</span>
                  </div>
                  <p class="c-text">{{ comment.content }}</p>
                </div>
              </div>
              <div v-if="comments.length === 0" class="empty-state">
                🌿 Soyez le premier à commenter !
              </div>
            </div>
          </div>


        </div>
      </main>
    </div>


    <!-- Loading / not found -->
    <div v-else-if="loading" class="loading-screen">
      <div class="spin-paw">🐾</div>
      <p>Chargement…</p>
    </div>
    <div v-else class="loading-screen"><p>Post non trouvé.</p></div>
  </div>


  <!-- ══════════════════════════════════════
       ADOPTION MODAL
  ══════════════════════════════════════ -->
  <Teleport to="body">
    <Transition name="overlay-fade">
      <div v-if="showAdoptionModal" class="modal-overlay" @click.self="showAdoptionModal = false">
        <Transition name="modal-pop" appear>
          <div class="modal" v-if="showAdoptionModal">
            <div v-if="adoptionSubmitted" class="modal-success">
              <div class="success-blob">✓</div>
              <h2>C'est parti ! 🎉</h2>
              <p>Nous vous contacterons très bientôt. Merci d'ouvrir votre cœur.</p>
              <button class="btn-publish" @click="closeAndResetAdoption">Fermer</button>
            </div>
            <div v-else>
              <div class="modal-header">
                <div class="modal-chip">🐾 Adoption</div>
                <h2 class="modal-title">Votre compagnon<br/>vous attend.</h2>
                <p class="modal-sub">Remplissez le formulaire pour commencer l'aventure.</p>
                <button class="modal-close" @click="showAdoptionModal = false">✕</button>
                <div class="modal-header-deco">🐕</div>
              </div>
              <div class="modal-body">
                <div class="mcard mc-cream">
                  <div class="mcard-head">👤 Informations personnelles</div>
                  <div class="mrow2">
                    <div class="mfield"><label>Nom complet</label><input type="text" v-model="adoptionForm.name" placeholder="Jane Dupont"/></div>
                    <div class="mfield"><label>Téléphone</label><input type="tel" v-model="adoptionForm.phoneNumber" placeholder="+216 00 000 000"/></div>
                  </div>
                  <div class="mfield"><label>Adresse e-mail</label><input type="email" v-model="adoptionForm.email" placeholder="jane@example.com"/></div>
                  <div class="mfield last"><label>Adresse postale</label><input type="text" v-model="adoptionForm.address" placeholder="123 Rue des Animaux, Tunis"/></div>
                </div>
                <div class="mcard mc-peach">
                  <div class="mcard-head">🏡 Votre logement</div>
                  <p class="mcard-desc">Aidez-nous à comprendre l'environnement que votre animal va rejoindre.</p>
                  <div class="mfield">
                    <label>Type de logement</label>
                    <div class="select-wrap">
                      <select v-model="adoptionForm.housingType">
                        <option value="maison">Maison</option>
                        <option value="appartement">Appartement</option>
                        <option value="villa">Villa</option>
                        <option value="autre">Autre</option>
                      </select>
                    </div>
                  </div>
                  <div class="mfield last">
                    <label>Jardin clôturé ?</label>
                    <div class="pill-group">
                      <span class="toggle-pill" :class="{ on: adoptionForm.hasGarden === true }" @click="adoptionForm.hasGarden = true">Oui</span>
                      <span class="toggle-pill" :class="{ on: adoptionForm.hasGarden === false }" @click="adoptionForm.hasGarden = false">Non</span>
                    </div>
                  </div>
                </div>
                <div class="mcard mc-blue">
                  <div class="mcard-head">🐾 Autres animaux</div>
                  <p class="mcard-desc">Avez-vous actuellement d'autres animaux ?</p>
                  <div class="pet-pills">
                    <span v-for="pet in petOptions" :key="pet.value" class="pet-pill" :class="{ on: adoptionForm.pets.includes(pet.value) }" @click="togglePet(pet.value)">{{ pet.label }}</span>
                  </div>
                </div>
                <div class="mfield last">
                <label>Pourquoi adopter ?</label>
                <textarea v-model="adoptionForm.message" placeholder="Parlez-nous de votre foyer et de votre cœur…" rows="4"></textarea>
                <p class="hint">💡 Votre histoire nous aide à trouver la correspondance parfaite.</p>
              </div>
              </div>
              <div class="modal-footer">
                <p class="modal-quote">"Un animal de compagnie est l'amour inconditionnel à quatre pattes."</p>
                <div class="modal-footer-btns">
                  <button class="btn-draft" @click="showAdoptionModal = false">Annuler</button>
                  <button class="btn-publish" @click="submitAdoption" :disabled="adoptionLoading">
                    {{ adoptionLoading ? 'Envoi en cours…' : 'Soumettre ma candidature →' }}
                  </button>
                </div>
              </div>
            </div>
          </div>
        </Transition>
      </div>
    </Transition>
  </Teleport>


  <!-- ══════════════════════════════════════
       REPORT MODAL (design untouched)
  ══════════════════════════════════════ -->
  <Teleport to="body">
    <Transition name="overlay-fade">
      <div v-if="showReportModal" class="modal-overlay" @click.self="closeReportModal">
        <Transition name="modal-pop" appear>
          <div class="modal report-modal" v-if="showReportModal">
            <div v-if="reportSubmitted" class="modal-success">
              <div class="success-blob">✓</div>
              <h2>Signalement envoyé !</h2>
              <p>Merci pour votre vigilance. Notre équipe va examiner ce post.</p>
              <button class="btn-publish" @click="closeReportModal">Fermer</button>
            </div>
            <div v-else>
              <div class="modal-header report-header">
                <div class="modal-chip report-chip">⚠️ Signalement</div>
                <h2 class="modal-title">Signaler ce post</h2>
                <p class="modal-sub">Dites-nous ce qui ne va pas. Nous prendrons les mesures nécessaires.</p>
                <button class="modal-close" @click="closeReportModal">✕</button>
                <div class="modal-header-deco">🚨</div>
              </div>
              <div class="report-modal-body">
                <div class="report-section">
                  <p class="report-section-label">Choisissez une raison</p>
                  <div class="reason-chips">
                    <span v-for="r in reportReasons" :key="r.value" class="reason-chip" :class="{ on: selectedReason === r.value }" @click="selectedReason = r.value">
                      {{ r.label }}
                    </span>
                  </div>
                </div>
                <div class="report-section">
                  <p class="report-section-label">Détails supplémentaires <span class="optional">(optionnel)</span></p>
                  <textarea v-model="reportReason" placeholder="Décrivez le problème plus en détail…" rows="4" class="report-textarea"></textarea>
                </div>
                <div v-if="reportMsg" class="alert-success" style="margin: 0 1.5rem;">{{ reportMsg }}</div>
              </div>
              <div class="report-modal-footer">
                <button class="btn-draft" @click="closeReportModal">Annuler</button>
                <button class="btn-publish red" @click="submitReport" :disabled="!selectedReason">Envoyer le signalement</button>
              </div>
            </div>
          </div>
        </Transition>
      </div>
    </Transition>
  </Teleport>
</template>


<script setup>
import { ref, onMounted, reactive } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { postsAPI, adoptionAPI, donationsAPI, commentsAPI, reportsAPI } from '../services/api'
import { useAuthStore } from '../stores/authStore'
import NavBar from '../components/NavBar.vue'


const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()


const post = ref(null)
const comments = ref([])
const loading = ref(true)


const showAdoptionModal = ref(false)
const adoptionSubmitted = ref(false)
const adoptionLoading = ref(false)
const adoptionMsg = ref(null)


const showDonationForm = ref(false)
const showReportModal = ref(false)
const reportSubmitted = ref(false)
const selectedReason = ref('')
const donationLoading = ref(false)
const donationMsg = ref(null)
const reportMsg = ref('')
const reportReason = ref('')
const newComment = ref('')


const reportReasons = [
  { value: 'fake',      label: 'Fausse annonce' },
  { value: 'abuse',      label: 'Contenu abusif' },
  { value: 'spam',      label: 'Spam' },
  { value: 'dangerous', label: 'Animal en danger' },
  { value: 'other',  label: 'Autre raison' },
]


const petOptions = [
  { label: '🚫 Aucun',             value: 'none' },
  { label: '🐕 Chiens',            value: 'dogs' },
  { label: '🐱 Chats',             value: 'cats' },
  { label: '🐹 Petits mammifères', value: 'small_mammals' },
]


const adoptionForm = reactive({
  message: '', housingType: 'maison', phoneNumber: '',
  name: '', email: '', address: '',
  hasExperience: false, hasGarden: false, hasChildren: false,
  pets: ['none'],
})


const donationForm = reactive({ cardNumber: '', cardHolderName: '', amount: '' })


const formatDate = (date) => (date ? new Date(date).toLocaleDateString('fr-FR') : '')


const togglePet = (value) => {
  if (value === 'none') { adoptionForm.pets = ['none']; return }
  const idx = adoptionForm.pets.indexOf(value)
  adoptionForm.pets = adoptionForm.pets.filter((p) => p !== 'none')
  if (idx === -1) adoptionForm.pets.push(value)
  else adoptionForm.pets.splice(adoptionForm.pets.indexOf(value), 1)
  if (adoptionForm.pets.length === 0) adoptionForm.pets = ['none']
}


const closeReportModal = () => {
  showReportModal.value = false
  reportSubmitted.value = false
  selectedReason.value = ''
  reportReason.value = ''
  reportMsg.value = ''
}


onMounted(async () => {
  try {
    const [postRes, commentsRes] = await Promise.all([
      postsAPI.getById(route.params.id),
      commentsAPI.getByPost(route.params.id),
    ])
    post.value = postRes.data
    comments.value = commentsRes.data
  } finally {
    loading.value = false
  }
})


const submitAdoption = async () => {
  adoptionLoading.value = true
  adoptionMsg.value = null
  try {
    await adoptionAPI.create({
      post_id: Number(route.params.id),
      message: adoptionForm.message,
      housing_type: adoptionForm.housingType,
      phone_number: adoptionForm.phoneNumber,
      has_experience: adoptionForm.hasExperience,
      has_garden: adoptionForm.hasGarden,
      has_children: adoptionForm.hasChildren,
    })
    adoptionSubmitted.value = true
  } catch (e) {
    adoptionMsg.value = { type: 'error', text: e.response?.data?.message || 'Erreur lors de la demande' }
  } finally {
    adoptionLoading.value = false
  }
}


const closeAndResetAdoption = () => {
  showAdoptionModal.value = false
  adoptionSubmitted.value = false
  Object.assign(adoptionForm, {
    message: '', housingType: 'maison', phoneNumber: '',
    name: '', email: '', address: '',
    hasExperience: false, hasGarden: false, hasChildren: false,
    pets: ['none'],
  })
}


const submitDonation = async () => {
  donationLoading.value = true
  donationMsg.value = null
  try {
    await donationsAPI.create({
      post_id: Number(route.params.id),
      card_number: donationForm.cardNumber,
      card_holder_name: donationForm.cardHolderName,
      amount: donationForm.amount,
    })
    donationMsg.value = { type: 'success', text: 'Don effectué avec succès !' }
    showDonationForm.value = false
  } catch (e) {
    donationMsg.value = { type: 'error', text: e.response?.data?.message || 'Erreur lors du don' }
  } finally {
    donationLoading.value = false
  }
}


const submitReport = async () => {
  try {
    const reason = selectedReason.value + (reportReason.value ? ': ' + reportReason.value : '')
    await reportsAPI.create({ post_id: Number(route.params.id), reason })
    reportSubmitted.value = true
    reportMsg.value = 'Signalement envoyé !'
  } catch (e) {
    reportMsg.value = e.response?.data?.message || 'Erreur lors du signalement'
  }
}


const submitComment = async () => {
  if (!newComment.value.trim()) return
  try {
    const res = await commentsAPI.create({ post_id: Number(route.params.id), content: newComment.value })
    comments.value.unshift(res.data)
    newComment.value = ''
  } catch (e) { console.error(e) }
}
</script>


<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;0,800;1,700&family=DM+Sans:wght@400;500;600&display=swap');


* { box-sizing: border-box; margin: 0; padding: 0; }


/* ─── PAGE SHELL ─── */
.page {
  min-height: 100vh;
  background: #fafaf8;
  font-family: 'DM Sans', sans-serif;
  color: #1a1a2e;
  display: flex;
  flex-direction: column;
}


.detail-wrapper {
  display: grid;
  grid-template-columns: 440px 1fr;
  flex: 1;
  align-items: stretch;
  min-height: calc(100vh - 64px);
}


/* ══════════ LEFT PANEL ══════════ */
.left-panel {
  background: #1a1a2e;
  position: sticky;
  top: 0;
  height: 100%;
  display: flex;
  flex-direction: column;
  gap: 1rem;
  padding: 2rem 1.8rem 2rem;
  overflow: hidden;
}


.paw-prints { position: absolute; inset: 0; pointer-events: none; z-index: 0; }
.paw { position: absolute; }
.lp1 { width: 66px; top: 4%;  left: 2%;  color: #f5d96b; opacity: .12; transform: rotate(-18deg); }
.lp2 { width: 50px; top: 18%; left: 60%; color: #f5d96b; opacity: .08; transform: rotate(30deg);  }
.lp3 { width: 74px; top: 36%; left: -2%; color: #fff;    opacity: .05; transform: rotate(-40deg); }
.lp4 { width: 58px; top: 52%; left: 70%; color: #f5d96b; opacity: .10; transform: rotate(12deg);  }
.lp5 { width: 44px; top: 68%; left: 20%; color: #fff;    opacity: .04; transform: rotate(55deg);  }
.lp6 { width: 62px; top: 80%; left: 55%; color: #f5d96b; opacity: .09; transform: rotate(-25deg); }
.lp7 { width: 48px; top: 12%; left: 38%; color: #fff;    opacity: .05; transform: rotate(42deg);  }
.lp8 { width: 70px; top: 62%; left: 8%;  color: #f5d96b; opacity: .07; transform: rotate(-8deg);  }
.lp9 { width: 52px; top: 90%; left: 80%; color: #fff;    opacity: .04; transform: rotate(70deg);  }


.back-btn {
  position: relative; z-index: 1;
  display: inline-flex; align-items: center; gap: 6px;
  background: rgba(255,255,255,.07);
  border: 1px solid rgba(255,255,255,.12);
  color: #f5d96b;
  font-size: .78rem; font-family: inherit; font-weight: 500;
  padding: .45rem .9rem; border-radius: 6px;
  cursor: pointer; align-self: flex-start;
  transition: background .2s;
}
.back-btn:hover { background: rgba(245,217,107,.15); }


.mfield textarea {
  background: #fff;
  border: 1.5px solid #e8e3d8;
  border-radius: 8px;
  padding: 10px 12px;
  font-size: .83rem;
  font-family: 'DM Sans', sans-serif;
  transition: border-color .2s, box-shadow .2s;
}

.mfield textarea:focus {
  border-color: #f5d96b;
  box-shadow: 0 0 0 3px rgba(245,217,107,.15);
}
.hero-photo-wrap {
  position: relative; z-index: 1;
  flex: 1;
  min-height: 220px;
  max-height: 320px;
  border-radius: 14px;
  overflow: hidden;
  border: 2px solid rgba(245,217,107,.25);
}
.hero-photo {
  width: 100%; height: 100%;
  object-fit: cover; display: block;
}
.hero-overlay {
  position: absolute; inset: 0;
  background: linear-gradient(to top, rgba(26,26,46,.6) 0%, transparent 60%);
}
.hero-photo-placeholder {
  width: 100%; height: 100%;
  display: flex; align-items: center; justify-content: center;
  font-size: 5rem;
  background: rgba(245,217,107,.07);
}
.status-chip {
  position: absolute; top: 12px; left: 12px;
  padding: .28rem .75rem; border-radius: 20px;
  font-size: .68rem; font-weight: 700; letter-spacing: .04em; z-index: 2;
}
.sc-available { background: rgba(40,200,120,.2); color: #4cde98; border: 1px solid rgba(76,222,152,.3); }
.sc-adopted   { background: rgba(255,255,255,.1); color: rgba(255,255,255,.55); border: 1px solid rgba(255,255,255,.18); }


/* Type pills */
.type-pills {
  position: relative; z-index: 1;
  display: flex; gap: .5rem; flex-wrap: wrap;
}
.tpill {
  font-size: .68rem; font-weight: 700;
  padding: .28rem .75rem; border-radius: 20px; letter-spacing: .03em;
}
.tpill-blue { background: rgba(100,180,255,.15); color: #8ecfff; border: 1px solid rgba(100,180,255,.25); }
.tpill-gold { background: rgba(245,217,107,.15); color: #f5d96b; border: 1px solid rgba(245,217,107,.25); }


/* Animal identity */
.animal-identity {
  position: relative; z-index: 1;
}
.animal-name {
  font-family: 'Playfair Display', serif;
  font-size: 1.5rem; font-weight: 800;
  color: #fff; line-height: 1.2; margin-bottom: 3px;
}
.animal-cat { font-size: .78rem; color: rgba(255,255,255,.38); }


/* Meta block */
.meta-block {
  position: relative; z-index: 1;
  display: flex; flex-direction: column;
}
.meta-row {
  display: flex; justify-content: space-between; align-items: center;
  padding: .45rem 0;
  border-bottom: 1px solid rgba(255,255,255,.06);
  font-size: .78rem;
}
.meta-row:last-child { border-bottom: none; }
.mk { color: rgba(255,255,255,.36); }
.mv { color: rgba(255,255,255,.82); font-weight: 600; text-align: right; max-width: 55%; }


/* Left footer */
.left-footer {
  position: relative; z-index: 1;
  margin-top: auto;
  padding-top: .5rem;
  border-top: 1px solid rgba(255,255,255,.06);
}
.left-tagline {
  font-size: .74rem; color: rgba(255,255,255,.28);
  font-style: italic; line-height: 1.5;
}


/* ══════════ RIGHT PANEL ══════════ */
.right-panel {
  position: relative;
  background: #fafaf8;
  overflow-y: auto;
}


.paw-prints-right { position: fixed; inset: 0; pointer-events: none; z-index: 0; }
.rp1 { width: 60px; top: 8%;  right: 2%;  color: #c8a84b; opacity: .08; transform: rotate(20deg); }
.rp2 { width: 46px; top: 25%; right: 18%; color: #c8a84b; opacity: .06; transform: rotate(-35deg); }
.rp3 { width: 70px; top: 50%; right: 0%;  color: #c8a84b; opacity: .07; transform: rotate(10deg); }
.rp4 { width: 52px; top: 72%; right: 12%; color: #c8a84b; opacity: .05; transform: rotate(-50deg); }
.rp5 { width: 64px; top: 15%; right: 38%; color: #c8a84b; opacity: .05; transform: rotate(60deg); }
.rp6 { width: 44px; top: 88%; right: 35%; color: #c8a84b; opacity: .06; transform: rotate(-15deg); }


.right-body {
  position: relative; z-index: 1;
  max-width: 640px;
  margin: 0 auto;
  padding: 3rem 2.5rem 4rem;
  display: flex; flex-direction: column; gap: 1.8rem;
}


/* ── Heading ── */
.post-eyebrow {
  display: block;
  font-size: .72rem; font-weight: 700;
  letter-spacing: .14em; text-transform: uppercase;
  color: #c8a84b; margin-bottom: .6rem;
}
.post-title {
  font-family: 'Playfair Display', serif;
  font-size: clamp(1.7rem, 3.2vw, 2.4rem);
  line-height: 1.2; color: #1a1a2e; font-weight: 800;
}
.wavy-line { width: 160px; margin-top: .8rem; }
.wavy-line svg { width: 100%; display: block; }


/* ── Description card ── */
.desc-card {
  background: #fff;
  border: 1.5px solid #e8e3d8;
  border-radius: 14px;
  padding: 1.4rem 1.6rem;
  display: flex;
  gap: 1rem;
  align-items: flex-start;
}
.desc-icon {
  font-size: 1.4rem;
  flex-shrink: 0;
  margin-top: 2px;
  opacity: .7;
}
.post-desc {
  font-size: .9rem; line-height: 1.8; color: #555;
}


.rule {
  height: 1px;
  background: linear-gradient(90deg, #f0ebe0 0%, transparent 100%);
}


/* ── Actions ── */
.actions-section { display: flex; flex-direction: column; gap: .75rem; }
.actions-label {
  font-size: .7rem; font-weight: 800;
  text-transform: uppercase; letter-spacing: .1em;
  color: #bbb;
}


.action-card {
  display: flex; align-items: center; gap: 12px;
  padding: 1rem 1.2rem;
  border-radius: 12px;
  border: 1.5px solid #e8e3d8;
  background: #fff;
  transition: all .2s;
}
.action-card:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(26,26,46,.08); }
.ac-adopt  { border-color: #c8dff8; background: #f0f7ff; }
.ac-donate { border-color: #f5d5a8; background: #fff7ee; }


.ac-icon-wrap {
  width: 44px; height: 44px; border-radius: 12px;
  background: rgba(100,160,255,.15);
  display: flex; align-items: center; justify-content: center;
  color: #3a7dc9; flex-shrink: 0;
}
.ac-icon-wrap.gold {
  background: rgba(200,168,75,.15);
  color: #c8a84b;
}
.ac-text { display: flex; flex-direction: column; flex: 1; gap: 2px; }
.ac-label { font-weight: 700; font-size: .84rem; color: #1a1a2e; }
.ac-sub   { font-size: .73rem; color: #888; }


/* Shared buttons */
.btn-publish {
  background: #1a1a2e;
  color: #fff; border: none;
  padding: .6rem 1.4rem; border-radius: 8px;
  font-size: .82rem; font-weight: 600;
  cursor: pointer; font-family: inherit;
  white-space: nowrap;
  transition: background .2s, transform .15s;
}
.btn-publish:hover:not(:disabled) { background: #c8a84b; transform: translateY(-1px); }
.btn-publish:disabled { opacity: .6; cursor: not-allowed; }
.btn-publish.sm { padding: .45rem .9rem; font-size: .78rem; }
.btn-publish.red { background: #e05252; }
.btn-publish.red:hover:not(:disabled) { background: #c03030; }


.btn-draft {
  display: inline-flex; align-items: center; gap: 6px;
  background: #f5f0e8; border: none;
  padding: .6rem 1.2rem; border-radius: 8px;
  font-size: .82rem; color: #1a1a2e;
  cursor: pointer; font-family: inherit;
  transition: background .2s; white-space: nowrap;
}
.btn-draft:hover { background: #ede5d0; }


/* Donation form */
.donation-form-box {
  background: #fff; border: 1.5px solid #e8e3d8;
  border-radius: 12px; padding: 1.2rem;
  display: flex; flex-direction: column; gap: .9rem;
}
.dfbox-head {
  font-size: .72rem; font-weight: 800;
  text-transform: uppercase; letter-spacing: .08em; color: #888;
}
.donation-grid { display: grid; grid-template-columns: 1fr 1fr; gap: .75rem; }
.dfield { display: flex; flex-direction: column; gap: 4px; }
.dfield.full { grid-column: 1/-1; }
.dfield label {
  font-size: .7rem; font-weight: 700;
  text-transform: uppercase; letter-spacing: .04em; color: #999;
}
.dfield input {
  padding: .65rem .9rem;
  border: 1.5px solid #e8e3d8; border-radius: 8px;
  font-family: inherit; font-size: .84rem; color: #1a1a2e;
  outline: none;
  transition: border-color .2s, box-shadow .2s;
}
.dfield input:focus {
  border-color: #f5d96b;
  box-shadow: 0 0 0 3px rgba(245,217,107,.15);
}
.donation-actions { display: flex; gap: .6rem; justify-content: flex-end; }


/* Report trigger */
.report-trigger {
  display: inline-flex; align-items: center; gap: 6px;
  background: none; border: none;
  color: #bbb; font-size: .78rem; font-family: inherit;
  cursor: pointer; padding: .25rem 0;
  transition: color .2s; align-self: flex-start;
}
.report-trigger:hover { color: #e05252; }


/* Alerts */
.alert-success {
  padding: .6rem .9rem; border-radius: 8px;
  background: #f0faf4; color: #3a9e68; border: 1px solid #c3e6d4; font-size: .8rem;
}
.alert-error {
  padding: .6rem .9rem; border-radius: 8px;
  background: #fff0f0; color: #e05252; border: 1px solid #fcd4d4; font-size: .8rem;
}


/* ── Comments ── */
.comments-section { display: flex; flex-direction: column; gap: 1rem; }
.comments-hd { display: flex; align-items: center; gap: 8px; }
.comments-hd h3 { font-size: .95rem; font-weight: 700; color: #1a1a2e; }
.comments-hd svg { color: #c8a84b; }


.compose-bar {
  display: flex; gap: .5rem;
  background: #fff; border: 1.5px solid #e8e3d8;
  border-radius: 30px; padding: .3rem .3rem .3rem 1rem;
  align-items: center;
}
.compose-bar input {
  flex: 1; border: none; background: transparent;
  font-family: inherit; font-size: .84rem; outline: none; color: #1a1a2e;
}
.compose-bar input::placeholder { color: #ccc; }


.comment-list { display: flex; flex-direction: column; gap: .7rem; }
.comment-item { display: flex; gap: .75rem; align-items: flex-start; }
.c-avatar {
  width: 38px; height: 38px;
  background: rgba(200,168,75,.12);
  border: 2px solid rgba(200,168,75,.35);
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-size: .82rem; font-weight: 800; color: #c8a84b; flex-shrink: 0;
}
.c-bubble {
  background: #fff; border: 1px solid #e8e3d8;
  border-radius: 0 14px 14px 14px;
  padding: .65rem .9rem; flex: 1;
}
.c-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: .25rem; }
.c-name { font-size: .78rem; font-weight: 700; color: #1a1a2e; }
.c-date { font-size: .68rem; color: #bbb; }
.c-text { font-size: .83rem; color: #555; line-height: 1.55; }


.empty-state { text-align: center; padding: 2rem; font-size: .85rem; color: #aaa; }


/* Loading */
.loading-screen {
  display: flex; flex-direction: column;
  align-items: center; justify-content: center;
  min-height: 60vh; gap: 1rem; color: #888;
}
.spin-paw { font-size: 3rem; animation: bounce 1.2s ease-in-out infinite; }
@keyframes bounce {
  0%,100% { transform: translateY(0); }
  50%      { transform: translateY(-10px); }
}


/* Slide transition */
.slide-enter-active, .slide-leave-active { transition: all .25s ease; }
.slide-enter-from, .slide-leave-to { opacity: 0; transform: translateY(-8px); }


/* ══════════ MODALS ══════════ */
.overlay-fade-enter-active, .overlay-fade-leave-active { transition: opacity .25s; }
.overlay-fade-enter-from, .overlay-fade-leave-to { opacity: 0; }
.modal-pop-enter-active { transition: all .35s cubic-bezier(.16,1.3,.3,1); }
.modal-pop-leave-active { transition: all .2s ease; }
.modal-pop-enter-from   { opacity: 0; transform: scale(.92) translateY(20px); }
.modal-pop-leave-to     { opacity: 0; transform: scale(.96); }


.modal-overlay {
  position: fixed; inset: 0;
  background: rgba(26,26,46,.52);
  backdrop-filter: blur(5px);
  z-index: 1000;
  display: flex; align-items: center; justify-content: center;
  padding: 1rem;
}
.modal {
  background: #fff; border-radius: 22px;
  width: 100%; max-width: 600px;
  max-height: 92vh; overflow-y: auto;
  box-shadow: 0 40px 100px rgba(26,26,46,.22);
  font-family: 'DM Sans', sans-serif;
}


/* Modal header */
.modal-header {
  position: relative;
  background: linear-gradient(135deg, #1a1a2e 0%, #2d2d50 100%);
  padding: 2rem 2rem 1.8rem;
  border-radius: 22px 22px 0 0;
  overflow: hidden;
}
.modal-chip {
  display: inline-flex; align-items: center; gap: 6px;
  background: rgba(245,217,107,.2); color: #f5d96b;
  border: 1px solid rgba(245,217,107,.35); border-radius: 20px;
  padding: .28rem .8rem; font-size: .68rem; font-weight: 800;
  text-transform: uppercase; letter-spacing: .06em; margin-bottom: .75rem;
}
.modal-title {
  font-family: 'Playfair Display', serif;
  font-size: 1.6rem; font-weight: 800;
  color: #fff; line-height: 1.2; margin-bottom: .5rem;
}
.modal-sub { font-size: .82rem; color: rgba(255,255,255,.48); }
.modal-close {
  position: absolute; top: 14px; right: 16px;
  background: rgba(255,255,255,.1); border: none;
  color: rgba(255,255,255,.6);
  width: 32px; height: 32px; border-radius: 50%;
  cursor: pointer; font-size: 13px;
  display: flex; align-items: center; justify-content: center;
  transition: background .2s;
}
.modal-close:hover { background: rgba(255,255,255,.2); color: #fff; }
.modal-header-deco {
  position: absolute; right: 2rem; bottom: -8px;
  font-size: 4.5rem; opacity: .1; pointer-events: none; user-select: none;
}


/* Modal body */
.modal-body { padding: 1.2rem 1.4rem 0; display: flex; flex-direction: column; gap: .75rem; }
.mcard { border-radius: 14px; padding: 1rem 1.2rem; }
.mc-cream { background: #fafaf8; border: 1.5px solid #e8e3d8; }
.mc-peach { background: #fde8d8; border: 1.5px solid #f5c9ab; }
.mc-blue  { background: #ddeeff; border: 1.5px solid #b8d6f5; }


.mcard-head {
  font-size: .7rem; font-weight: 800;
  text-transform: uppercase; letter-spacing: .08em;
  color: #1a1a2e; margin-bottom: 10px;
}
.mc-peach .mcard-head { color: #c45c1a; }
.mc-blue  .mcard-head { color: #1a5fa0; }
.mcard-desc { font-size: .78rem; color: #888; line-height: 1.5; margin-bottom: 10px; }


.mrow2 { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
.mfield { display: flex; flex-direction: column; gap: 4px; margin-bottom: 8px; }
.mfield.last { margin-bottom: 0; }
.mfield label {
  font-size: .68rem; font-weight: 700;
  text-transform: uppercase; letter-spacing: .05em; color: #999;
}
.mfield input, .mfield textarea, .mfield select {
  padding: 9px 12px;
  border: 1.5px solid #e8e3d8; border-radius: 8px;
  font-family: 'DM Sans', sans-serif; font-size: .83rem;
  color: #1a1a2e; background: #fff;
  outline: none; appearance: none; width: 100%;
  transition: border-color .18s, box-shadow .18s;
}
.mfield input:focus, .mfield textarea:focus, .mfield select:focus {
  border-color: #f5d96b;
  box-shadow: 0 0 0 3px rgba(245,217,107,.15);
}
.mfield textarea { resize: none; min-height: 80px; }


.select-wrap { position: relative; }
.select-wrap select {
  padding-right: 30px;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='7' viewBox='0 0 12 7'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%23999' stroke-width='1.5' fill='none' stroke-linecap='round'/%3E%3C/svg%3E");
  background-repeat: no-repeat; background-position: right 10px center; cursor: pointer;
}


.pill-group { display: flex; gap: 8px; }
.toggle-pill {
  padding: 7px 18px; border: 1.5px solid #e8e3d8;
  border-radius: 30px; cursor: pointer; font-size: .78rem;
  font-weight: 700; color: #888; background: #fff;
  transition: all .15s; user-select: none;
}
.toggle-pill.on { background: #1a1a2e; color: #fff; border-color: #1a1a2e; }


.pet-pills { display: flex; flex-wrap: wrap; gap: 7px; }
.pet-pill {
  padding: 6px 14px; border: 1.5px solid #b8d6f5;
  border-radius: 30px; cursor: pointer; font-size: .78rem;
  font-weight: 700; color: #1a5fa0; background: #fff;
  transition: all .15s; user-select: none;
}
.pet-pill.on { background: #1a5fa0; color: #fff; border-color: #1a5fa0; }


.hint { font-size: .72rem; color: #999; margin-top: 7px; }


/* Modal footer */
.modal-footer {
  padding: 1.2rem 1.4rem 1.6rem;
  border-top: 1px solid #f0ebe0;
  margin-top: .5rem;
  display: flex; flex-direction: column; gap: .8rem;
}
.modal-quote {
  font-family: 'Playfair Display', serif;
  font-style: italic; font-size: .78rem;
  color: #bbb; text-align: center;
}
.modal-footer-btns {
  display: flex; justify-content: flex-end; gap: .6rem;
}


/* Modal success */
.modal-success {
  display: flex; flex-direction: column; align-items: center;
  justify-content: center; padding: 3.5rem 2rem; text-align: center; gap: 1rem;
}
.success-blob {
  width: 76px; height: 76px; border-radius: 50%;
  background: #e8faf0; border: 3px solid #3a9e68;
  display: flex; align-items: center; justify-content: center;
  font-size: 2rem; color: #3a9e68;
  animation: popIn .4s cubic-bezier(.16,1.3,.3,1) both;
}
@keyframes popIn {
  from { transform: scale(.4); opacity: 0; }
  to   { transform: scale(1);  opacity: 1; }
}
.modal-success h2 {
  font-family: 'Playfair Display', serif; font-size: 1.7rem; color: #1a1a2e;
}
.modal-success p { font-size: .88rem; color: #888; line-height: 1.6; max-width: 270px; }


/* ── Report modal ── */
.report-modal { max-width: 500px; }
.report-header { background: linear-gradient(135deg, #2d1e1e 0%, #4a2020 100%) !important; }
.report-chip {
  background: rgba(224,82,82,.25) !important;
  color: #f4a0a0 !important;
  border-color: rgba(224,82,82,.4) !important;
}
.report-modal-body { padding: 1.4rem 1.4rem .5rem; display: flex; flex-direction: column; gap: 1.2rem; }
.report-section-label {
  font-size: .72rem; font-weight: 800;
  text-transform: uppercase; letter-spacing: .07em;
  color: #999; margin-bottom: .6rem;
}
.optional { font-weight: 400; text-transform: none; letter-spacing: 0; color: #ccc; }
.reason-chips { display: flex; flex-wrap: wrap; gap: 8px; }
.reason-chip {
  padding: .45rem 1rem; border: 1.5px solid #e8e3d8;
  border-radius: 30px; font-size: .8rem; font-weight: 700;
  color: #888; background: #fff; cursor: pointer;
  transition: all .15s; user-select: none;
}
.reason-chip:hover { border-color: #e05252; color: #e05252; }
.reason-chip.on { background: #ffeaea; color: #c0392b; border-color: #e05252; }
.report-textarea {
  width: 100%; padding: .75rem 1rem;
  border: 1.5px solid #e8e3d8; border-radius: 8px;
  font-family: 'DM Sans', sans-serif; font-size: .85rem;
  color: #1a1a2e; background: #fafaf8; outline: none; resize: none;
  transition: border-color .2s, box-shadow .2s;
}
.report-textarea:focus { border-color: #e05252; box-shadow: 0 0 0 3px rgba(224,82,82,.1); background: #fff; }
.report-modal-footer {
  display: flex; align-items: center; justify-content: flex-end; gap: .75rem;
  padding: 1rem 1.4rem 1.4rem;
  border-top: 1px solid #e8e3d8; margin-top: 1rem;
}


/* ── Responsive ── */
@media (max-width: 900px) {
  .detail-wrapper { grid-template-columns: 1fr; }
  .left-panel { position: relative; height: auto; }
  .hero-photo-wrap { min-height: 200px; max-height: 280px; }
  .paw-prints-right { display: none; }
  .right-body { padding: 2rem 1.5rem 3rem; }
  .mrow2 { grid-template-columns: 1fr; }
  .donation-grid { grid-template-columns: 1fr; }
}
@media (max-width: 600px) {
  .modal-header { padding: 1.5rem 1.2rem 1.3rem; }
  .modal-title  { font-size: 1.3rem; }
}
</style>
