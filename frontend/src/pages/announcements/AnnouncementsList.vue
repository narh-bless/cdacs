<template>
  <q-page class="q-pa-md">
    <!-- Header -->
    <div class="row items-center justify-between q-mb-md">
      <div class="text-h4">Announcements</div>
      <q-btn
        v-if="canCreateAnnouncement"
        color="primary"
        icon="add"
        label="New Announcement"
        @click="showCreateDialog = true"
      />
    </div>

    <!-- Filters & Search -->
    <q-card class="q-mb-md">
      <q-card-section>
        <div class="row q-col-gutter-md">
          <div class="col-12 col-md-4">
            <q-input
              v-model="search"
              outlined
              dense
              placeholder="Search announcements..."
              @update:model-value="debouncedSearch"
            >
              <template v-slot:prepend>
                <q-icon name="search" />
              </template>
              <template v-slot:append v-if="search">
                <q-icon name="clear" class="cursor-pointer" @click="clearSearch" />
              </template>
            </q-input>
          </div>
          <div class="col-12 col-sm-6 col-md-4">
            <q-select
              v-model="filters.priority"
              outlined
              dense
              label="Priority"
              :options="priorityOptions"
              emit-value
              map-options
              clearable
              @update:model-value="fetchAnnouncements"
            />
          </div>
          <div class="col-12 col-sm-6 col-md-4">
            <q-select
              v-model="filters.status"
              outlined
              dense
              label="Status"
              :options="statusOptions"
              emit-value
              map-options
              clearable
              @update:model-value="fetchAnnouncements"
            />
          </div>
        </div>
      </q-card-section>
    </q-card>

    <!-- Announcements List -->
    <div v-if="loading" class="text-center q-py-xl">
      <q-spinner color="primary" size="50px" />
    </div>

    <div v-else-if="announcements.length === 0" class="text-center q-py-xl">
      <q-icon name="campaign" size="64px" class="text-grey-5 q-mb-md" />
      <div class="text-h6 text-grey-6">No announcements found</div>
      <div class="text-grey-5">
        {{ search ? 'Try adjusting your search criteria' : 'Create your first announcement to get started' }}
      </div>
    </div>

    <div v-else class="row q-col-gutter-md">
      <div
        v-for="announcement in announcements"
        :key="announcement.id"
        class="col-12"
      >
        <q-card class="announcement-card" :class="getPriorityClass(announcement.priority)">
          <q-card-section>
            <div class="row items-start justify-between">
              <div class="col">
                <div class="row items-center q-mb-xs">
                  <q-chip
                    :color="getPriorityColor(announcement.priority)"
                    text-color="white"
                    size="sm"
                    class="q-mr-sm"
                  >
                    {{ announcement.priority || 'medium' }}
                  </q-chip>
                  <q-chip
                    :color="announcement.is_published ? 'positive' : 'grey'"
                    text-color="white"
                    size="sm"
                  >
                    {{ announcement.is_published ? 'Published' : 'Draft' }}
                  </q-chip>
                </div>
                <div class="text-h6 q-mb-sm">{{ announcement.title }}</div>
                <div class="text-body2 text-grey-7 q-mb-sm" style="white-space: pre-line">
                  {{ announcement.content }}
                </div>
                <div class="text-caption text-grey-6">
                  <q-icon name="person" size="16px" class="q-mr-xs" />
                  {{ announcement.author?.name || 'Unknown' }}
                  <span class="q-mx-sm">•</span>
                  <q-icon name="schedule" size="16px" class="q-mr-xs" />
                  {{ formatDate(announcement.published_at || announcement.created_at) }}
                </div>
              </div>
              <div class="col-auto q-pl-md" v-if="canManageAnnouncement(announcement)">
                <q-btn-dropdown
                  flat
                  round
                  dense
                  icon="more_vert"
                  dropdown-icon="none"
                >
                  <q-list>
                    <q-item
                      v-if="!announcement.is_published"
                      clickable
                      v-close-popup
                      @click="publishAnnouncement(announcement)"
                    >
                      <q-item-section avatar>
                        <q-icon name="publish" />
                      </q-item-section>
                      <q-item-section>Publish</q-item-section>
                    </q-item>
                    <q-item clickable v-close-popup @click="editAnnouncement(announcement)">
                      <q-item-section avatar>
                        <q-icon name="edit" />
                      </q-item-section>
                      <q-item-section>Edit</q-item-section>
                    </q-item>
                    <q-separator />
                    <q-item
                      clickable
                      v-close-popup
                      @click="deleteAnnouncement(announcement)"
                      class="text-negative"
                    >
                      <q-item-section avatar>
                        <q-icon name="delete" color="negative" />
                      </q-item-section>
                      <q-item-section>Delete</q-item-section>
                    </q-item>
                  </q-list>
                </q-btn-dropdown>
              </div>
            </div>
          </q-card-section>
        </q-card>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="pagination.last_page > 1" class="flex flex-center q-mt-md">
      <q-pagination
        v-model="pagination.current_page"
        :max="pagination.last_page"
        :max-pages="6"
        boundary-numbers
        @update:model-value="fetchAnnouncements"
      />
    </div>

    <!-- Create/Edit Dialog -->
    <q-dialog v-model="showCreateDialog" persistent>
      <q-card style="min-width: 500px">
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6">{{ editingAnnouncement ? 'Edit' : 'Create' }} Announcement</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>

        <q-card-section>
          <q-form @submit="saveAnnouncement" class="q-gutter-md">
            <q-input
              v-model="form.title"
              outlined
              label="Title *"
              :rules="[val => !!val || 'Title is required']"
            />

            <q-input
              v-model="form.content"
              outlined
              type="textarea"
              label="Content *"
              rows="5"
              :rules="[val => !!val || 'Content is required']"
            />

            <q-select
              v-model="form.priority"
              outlined
              label="Priority"
              :options="priorityOptions"
              emit-value
              map-options
            />

            <q-toggle
              v-model="form.is_published"
              label="Publish immediately"
              color="positive"
            />

            <div class="row q-gutter-sm justify-end">
              <q-btn label="Cancel" flat v-close-popup />
              <q-btn
                type="submit"
                label="Save"
                color="primary"
                :loading="saving"
              />
            </div>
          </q-form>
        </q-card-section>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useQuasar } from 'quasar'
import { api } from 'src/services/api'
import { useAuthStore } from 'src/stores/auth'
import { date } from 'quasar'

const $q = useQuasar()
const authStore = useAuthStore()

// Data
const announcements = ref([])
const loading = ref(false)
const saving = ref(false)
const search = ref('')
const filters = ref({
  priority: null,
  status: null
})
const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0
})

// Dialog & Forms
const showCreateDialog = ref(false)
const editingAnnouncement = ref(null)
const form = ref({
  title: '',
  content: '',
  priority: 'medium',
  is_published: false
})

// Options
const priorityOptions = [
  { label: 'Low', value: 'low' },
  { label: 'Medium', value: 'medium' },
  { label: 'High', value: 'high' }
]

const statusOptions = [
  { label: 'Published', value: 'published' },
  { label: 'Draft', value: 'draft' }
]

// Computed
const canCreateAnnouncement = computed(() => {
  return authStore.hasAnyRole(['pastor', 'administrator'])
})

// Methods
function canManageAnnouncement(announcement) {
  if (authStore.hasRole('administrator')) return true
  if (authStore.hasRole('pastor')) return true
  return announcement.author_id === authStore.user?.id
}

async function fetchAnnouncements() {
  loading.value = true
  try {
    const params = {
      page: pagination.value.current_page,
      per_page: pagination.value.per_page
    }

    if (search.value) {
      params.search = search.value
    }
    if (filters.value.priority) {
      params.priority = filters.value.priority
    }
    if (filters.value.status) {
      params.is_published = filters.value.status === 'published' ? 1 : 0
    }

    const response = await api.get('/announcements', { params })
    
    announcements.value = response.data.data
    pagination.value = {
      current_page: response.data.current_page,
      last_page: response.data.last_page,
      per_page: response.data.per_page,
      total: response.data.total
    }
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: 'Failed to load announcements',
      caption: error.response?.data?.message || error.message
    })
  } finally {
    loading.value = false
  }
}

let searchTimeout = null
function debouncedSearch() {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    pagination.value.current_page = 1
    fetchAnnouncements()
  }, 500)
}

function clearSearch() {
  search.value = ''
  fetchAnnouncements()
}

function editAnnouncement(announcement) {
  editingAnnouncement.value = announcement
  form.value = {
    title: announcement.title,
    content: announcement.content,
    priority: announcement.priority || 'medium',
    is_published: announcement.is_published
  }
  showCreateDialog.value = true
}

async function saveAnnouncement() {
  saving.value = true
  try {
    if (editingAnnouncement.value) {
      await api.put(`/announcements/${editingAnnouncement.value.id}`, form.value)
      $q.notify({
        type: 'positive',
        message: 'Announcement updated successfully'
      })
    } else {
      await api.post('/announcements', form.value)
      $q.notify({
        type: 'positive',
        message: 'Announcement created successfully'
      })
    }
    
    showCreateDialog.value = false
    resetForm()
    fetchAnnouncements()
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: 'Failed to save announcement',
      caption: error.response?.data?.message || error.message
    })
  } finally {
    saving.value = false
  }
}

async function publishAnnouncement(announcement) {
  try {
    await api.post(`/announcements/${announcement.id}/publish`)
    $q.notify({
      type: 'positive',
      message: 'Announcement published successfully'
    })
    fetchAnnouncements()
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: 'Failed to publish announcement',
      caption: error.response?.data?.message || error.message
    })
  }
}

function deleteAnnouncement(announcement) {
  $q.dialog({
    title: 'Confirm Delete',
    message: `Are you sure you want to delete "${announcement.title}"?`,
    cancel: true,
    persistent: true
  }).onOk(async () => {
    try {
      await api.delete(`/announcements/${announcement.id}`)
      $q.notify({
        type: 'positive',
        message: 'Announcement deleted successfully'
      })
      fetchAnnouncements()
    } catch (error) {
      $q.notify({
        type: 'negative',
        message: 'Failed to delete announcement',
        caption: error.response?.data?.message || error.message
      })
    }
  })
}

function resetForm() {
  editingAnnouncement.value = null
  form.value = {
    title: '',
    content: '',
    priority: 'medium',
    is_published: false
  }
}

function getPriorityColor(priority) {
  const colors = {
    high: 'negative',
    medium: 'warning',
    low: 'info'
  }
  return colors[priority] || 'warning'
}

function getPriorityClass(priority) {
  if (priority === 'high') return 'border-left-high'
  if (priority === 'low') return 'border-left-low'
  return 'border-left-medium'
}

function formatDate(dateString) {
  if (!dateString) return 'N/A'
  return date.formatDate(dateString, 'MMM DD, YYYY h:mm A')
}

// Lifecycle
onMounted(() => {
  fetchAnnouncements()
})
</script>

<style scoped>
.announcement-card {
  transition: all 0.3s;
}

.announcement-card:hover {
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.border-left-high {
  border-left: 4px solid #c10015;
}

.border-left-medium {
  border-left: 4px solid #f2c037;
}

.border-left-low {
  border-left: 4px solid #31ccec;
}
</style>
