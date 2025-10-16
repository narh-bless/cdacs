<template>
  <q-page class="q-pa-md">
    <!-- Header -->
    <div class="row items-center justify-between q-mb-md">
      <div class="text-h4">Events</div>
      <q-btn
        v-if="canCreateEvent"
        color="primary"
        icon="add"
        label="New Event"
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
              placeholder="Search events..."
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
          <div class="col-12 col-sm-6 col-md-3">
            <q-select
              v-model="filters.type"
              outlined
              dense
              label="Event Type"
              :options="typeOptions"
              emit-value
              map-options
              clearable
              @update:model-value="fetchEvents"
            />
          </div>
          <div class="col-12 col-sm-6 col-md-2">
            <q-select
              v-model="filters.timeFilter"
              outlined
              dense
              label="Time"
              :options="timeFilterOptions"
              emit-value
              map-options
              clearable
              @update:model-value="fetchEvents"
            />
          </div>
          <div class="col-12 col-sm-6 col-md-3">
            <q-select
              v-model="filters.status"
              outlined
              dense
              label="Status"
              :options="statusOptions"
              emit-value
              map-options
              clearable
              @update:model-value="fetchEvents"
            />
          </div>
        </div>
      </q-card-section>
    </q-card>

    <!-- Events List -->
    <div v-if="loading" class="text-center q-py-xl">
      <q-spinner color="primary" size="50px" />
    </div>

    <div v-else-if="events.length === 0" class="text-center q-py-xl">
      <q-icon name="event" size="64px" class="text-grey-5 q-mb-md" />
      <div class="text-h6 text-grey-6">No events found</div>
      <div class="text-grey-5">
        {{ search ? 'Try adjusting your search criteria' : 'Create your first event to get started' }}
      </div>
    </div>

    <div v-else class="row q-col-gutter-md">
      <div
        v-for="event in events"
        :key="event.id"
        class="col-12 col-md-6"
      >
        <q-card class="event-card" :class="getEventTypeClass(event.type)">
          <q-card-section>
            <div class="row items-start justify-between">
              <div class="col">
                <div class="row items-center q-mb-xs">
                  <q-chip
                    :color="getTypeColor(event.type)"
                    text-color="white"
                    size="sm"
                    class="q-mr-sm"
                  >
                    <q-icon name="category" size="16px" class="q-mr-xs" />
                    {{ event.type || 'event' }}
                  </q-chip>
                  <q-chip
                    :color="event.is_published ? 'positive' : 'grey'"
                    text-color="white"
                    size="sm"
                  >
                    {{ event.is_published ? 'Published' : 'Draft' }}
                  </q-chip>
                  <q-chip
                    v-if="event.is_recurring"
                    color="purple"
                    text-color="white"
                    size="sm"
                    class="q-ml-xs"
                  >
                    <q-icon name="repeat" size="16px" class="q-mr-xs" />
                    {{ event.recurrence_pattern }}
                  </q-chip>
                </div>
                <div class="text-h6 q-mb-sm">{{ event.title }}</div>
                <div class="text-body2 text-grey-7 q-mb-sm" style="white-space: pre-line">
                  {{ event.description }}
                </div>
                
                <!-- Event Details -->
                <div class="q-mt-md">
                  <div class="text-caption text-grey-8 q-mb-xs">
                    <q-icon name="schedule" size="16px" class="q-mr-xs" color="primary" />
                    <strong>Start:</strong> {{ formatDateTime(event.start_date) }}
                  </div>
                  <div class="text-caption text-grey-8 q-mb-xs">
                    <q-icon name="event_available" size="16px" class="q-mr-xs" color="primary" />
                    <strong>End:</strong> {{ formatDateTime(event.end_date) }}
                  </div>
                  <div v-if="event.location" class="text-caption text-grey-8">
                    <q-icon name="place" size="16px" class="q-mr-xs" color="primary" />
                    <strong>Location:</strong> {{ event.location }}
                  </div>
                </div>

                <div class="text-caption text-grey-6 q-mt-sm">
                  <q-icon name="person" size="16px" class="q-mr-xs" />
                  {{ event.organizer?.name || 'Unknown' }}
                  <span class="q-mx-sm">•</span>
                  Created {{ formatDate(event.created_at) }}
                </div>
              </div>
              <div class="col-auto q-pl-md" v-if="canManageEvent(event)">
                <q-btn-dropdown
                  flat
                  round
                  dense
                  icon="more_vert"
                  dropdown-icon="none"
                >
                  <q-list>
                    <q-item
                      v-if="!event.is_published"
                      clickable
                      v-close-popup
                      @click="publishEvent(event)"
                    >
                      <q-item-section avatar>
                        <q-icon name="publish" />
                      </q-item-section>
                      <q-item-section>Publish</q-item-section>
                    </q-item>
                    <q-item clickable v-close-popup @click="editEvent(event)">
                      <q-item-section avatar>
                        <q-icon name="edit" />
                      </q-item-section>
                      <q-item-section>Edit</q-item-section>
                    </q-item>
                    <q-separator />
                    <q-item
                      clickable
                      v-close-popup
                      @click="deleteEvent(event)"
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

          <!-- Time Until Event Badge -->
          <q-card-section v-if="getTimeUntilEvent(event)" class="q-pt-none">
            <q-banner dense class="bg-blue-1 text-primary">
              <template v-slot:avatar>
                <q-icon name="access_time" />
              </template>
              {{ getTimeUntilEvent(event) }}
            </q-banner>
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
        @update:model-value="fetchEvents"
      />
    </div>

    <!-- Create/Edit Dialog -->
    <q-dialog v-model="showCreateDialog" persistent>
      <q-card style="min-width: 600px; max-width: 800px">
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6">{{ editingEvent ? 'Edit' : 'Create' }} Event</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>

        <q-card-section>
          <q-form @submit="saveEvent" class="q-gutter-md">
            <q-input
              v-model="form.title"
              outlined
              label="Event Title *"
              :rules="[val => !!val || 'Title is required']"
            />

            <q-input
              v-model="form.description"
              outlined
              type="textarea"
              label="Description *"
              rows="4"
              :rules="[val => !!val || 'Description is required']"
            />

            <div class="row q-col-gutter-md">
              <div class="col-12 col-sm-6">
                <q-input
                  v-model="form.start_date"
                  outlined
                  label="Start Date & Time *"
                  :rules="[val => !!val || 'Start date is required']"
                >
                  <template v-slot:prepend>
                    <q-icon name="event" class="cursor-pointer">
                      <q-popup-proxy cover transition-show="scale" transition-hide="scale">
                        <q-date v-model="form.start_date" mask="YYYY-MM-DD HH:mm:ss">
                          <div class="row items-center justify-end">
                            <q-btn v-close-popup label="Close" color="primary" flat />
                          </div>
                        </q-date>
                      </q-popup-proxy>
                    </q-icon>
                  </template>
                  <template v-slot:append>
                    <q-icon name="access_time" class="cursor-pointer">
                      <q-popup-proxy cover transition-show="scale" transition-hide="scale">
                        <q-time v-model="form.start_date" mask="YYYY-MM-DD HH:mm:ss" with-seconds>
                          <div class="row items-center justify-end">
                            <q-btn v-close-popup label="Close" color="primary" flat />
                          </div>
                        </q-time>
                      </q-popup-proxy>
                    </q-icon>
                  </template>
                </q-input>
              </div>

              <div class="col-12 col-sm-6">
                <q-input
                  v-model="form.end_date"
                  outlined
                  label="End Date & Time *"
                  :rules="[val => !!val || 'End date is required']"
                >
                  <template v-slot:prepend>
                    <q-icon name="event" class="cursor-pointer">
                      <q-popup-proxy cover transition-show="scale" transition-hide="scale">
                        <q-date v-model="form.end_date" mask="YYYY-MM-DD HH:mm:ss">
                          <div class="row items-center justify-end">
                            <q-btn v-close-popup label="Close" color="primary" flat />
                          </div>
                        </q-date>
                      </q-popup-proxy>
                    </q-icon>
                  </template>
                  <template v-slot:append>
                    <q-icon name="access_time" class="cursor-pointer">
                      <q-popup-proxy cover transition-show="scale" transition-hide="scale">
                        <q-time v-model="form.end_date" mask="YYYY-MM-DD HH:mm:ss" with-seconds>
                          <div class="row items-center justify-end">
                            <q-btn v-close-popup label="Close" color="primary" flat />
                          </div>
                        </q-time>
                      </q-popup-proxy>
                    </q-icon>
                  </template>
                </q-input>
              </div>
            </div>

            <q-input
              v-model="form.location"
              outlined
              label="Location"
              hint="e.g., Church Fellowship Hall, Main Sanctuary"
            />

            <q-select
              v-model="form.type"
              outlined
              label="Event Type"
              :options="typeOptions"
              emit-value
              map-options
            />

            <div class="q-gutter-sm">
              <q-toggle
                v-model="form.is_recurring"
                label="Recurring Event"
                color="purple"
              />

              <q-select
                v-if="form.is_recurring"
                v-model="form.recurrence_pattern"
                outlined
                label="Recurrence Pattern"
                :options="recurrenceOptions"
                emit-value
                map-options
                class="q-mt-md"
              />
            </div>

            <q-toggle
              v-model="form.is_published"
              label="Publish immediately"
              color="positive"
            />

            <div class="row q-gutter-sm justify-end">
              <q-btn label="Cancel" flat v-close-popup @click="resetForm" />
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
import { useQuasar, date as qdate } from 'quasar'
import { api } from 'src/services/api'
import { useAuthStore } from 'src/stores/auth'

const $q = useQuasar()
const authStore = useAuthStore()

// Data
const events = ref([])
const loading = ref(false)
const saving = ref(false)
const search = ref('')
const filters = ref({
  type: null,
  timeFilter: null,
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
const editingEvent = ref(null)
const form = ref({
  title: '',
  description: '',
  start_date: '',
  end_date: '',
  location: '',
  type: 'meeting',
  is_recurring: false,
  recurrence_pattern: 'weekly',
  is_published: false
})

// Options
const typeOptions = [
  { label: 'Meeting', value: 'meeting' },
  { label: 'Service', value: 'service' },
  { label: 'Conference', value: 'conference' },
  { label: 'Workshop', value: 'workshop' },
  { label: 'Prayer Meeting', value: 'prayer_meeting' },
  { label: 'Bible Study', value: 'bible_study' },
  { label: 'Fellowship', value: 'fellowship' },
  { label: 'Outreach', value: 'outreach' },
  { label: 'Other', value: 'other' }
]

const timeFilterOptions = [
  { label: 'Upcoming', value: 'upcoming' },
  { label: 'Past', value: 'past' },
  { label: 'This Week', value: 'this_week' },
  { label: 'This Month', value: 'this_month' }
]

const statusOptions = [
  { label: 'Published', value: 'published' },
  { label: 'Draft', value: 'draft' }
]

const recurrenceOptions = [
  { label: 'Daily', value: 'daily' },
  { label: 'Weekly', value: 'weekly' },
  { label: 'Bi-weekly', value: 'biweekly' },
  { label: 'Monthly', value: 'monthly' },
  { label: 'Yearly', value: 'yearly' }
]

// Computed
const canCreateEvent = computed(() => {
  return authStore.hasAnyRole(['pastor', 'administrator'])
})

// Methods
function canManageEvent(event) {
  if (authStore.hasRole('administrator')) return true
  if (authStore.hasRole('pastor')) return true
  return event.organizer_id === authStore.user?.id
}

async function fetchEvents() {
  loading.value = true
  try {
    const params = {
      page: pagination.value.current_page,
      per_page: pagination.value.per_page
    }

    if (search.value) {
      params.search = search.value
    }
    if (filters.value.type) {
      params.type = filters.value.type
    }
    if (filters.value.status) {
      params.is_published = filters.value.status === 'published' ? 1 : 0
    }
    if (filters.value.timeFilter) {
      params.time_filter = filters.value.timeFilter
    }

    const response = await api.get('/events', { params })
    
    // Handle both paginated and non-paginated responses
    if (response.data.data && Array.isArray(response.data.data)) {
      events.value = response.data.data
      pagination.value = {
        current_page: response.data.current_page || 1,
        last_page: response.data.last_page || 1,
        per_page: response.data.per_page || 15,
        total: response.data.total || response.data.data.length
      }
    } else if (Array.isArray(response.data)) {
      events.value = response.data
      pagination.value = {
        current_page: 1,
        last_page: 1,
        per_page: 15,
        total: response.data.length
      }
    } else {
      events.value = []
      pagination.value = {
        current_page: 1,
        last_page: 1,
        per_page: 15,
        total: 0
      }
    }
  } catch (error) {
    events.value = []
    $q.notify({
      type: 'negative',
      message: 'Failed to load events',
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
    fetchEvents()
  }, 500)
}

function clearSearch() {
  search.value = ''
  fetchEvents()
}

function editEvent(event) {
  editingEvent.value = event
  form.value = {
    title: event.title,
    description: event.description,
    start_date: event.start_date,
    end_date: event.end_date,
    location: event.location || '',
    type: event.type || 'meeting',
    is_recurring: event.is_recurring || false,
    recurrence_pattern: event.recurrence_pattern || 'weekly',
    is_published: event.is_published
  }
  showCreateDialog.value = true
}

async function saveEvent() {
  saving.value = true
  try {
    if (editingEvent.value) {
      await api.put(`/events/${editingEvent.value.id}`, form.value)
      $q.notify({
        type: 'positive',
        message: 'Event updated successfully'
      })
    } else {
      await api.post('/events', form.value)
      $q.notify({
        type: 'positive',
        message: 'Event created successfully'
      })
    }
    
    showCreateDialog.value = false
    resetForm()
    fetchEvents()
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: 'Failed to save event',
      caption: error.response?.data?.message || error.message
    })
  } finally {
    saving.value = false
  }
}

async function publishEvent(event) {
  try {
    await api.post(`/events/${event.id}/publish`)
    $q.notify({
      type: 'positive',
      message: 'Event published successfully'
    })
    fetchEvents()
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: 'Failed to publish event',
      caption: error.response?.data?.message || error.message
    })
  }
}

function deleteEvent(event) {
  $q.dialog({
    title: 'Confirm Delete',
    message: `Are you sure you want to delete "${event.title}"?`,
    cancel: true,
    persistent: true
  }).onOk(async () => {
    try {
      await api.delete(`/events/${event.id}`)
      $q.notify({
        type: 'positive',
        message: 'Event deleted successfully'
      })
      fetchEvents()
    } catch (error) {
      $q.notify({
        type: 'negative',
        message: 'Failed to delete event',
        caption: error.response?.data?.message || error.message
      })
    }
  })
}

function resetForm() {
  editingEvent.value = null
  form.value = {
    title: '',
    description: '',
    start_date: '',
    end_date: '',
    location: '',
    type: 'meeting',
    is_recurring: false,
    recurrence_pattern: 'weekly',
    is_published: false
  }
}

function getTypeColor(type) {
  const colors = {
    meeting: 'blue',
    service: 'purple',
    conference: 'deep-orange',
    workshop: 'teal',
    prayer_meeting: 'indigo',
    bible_study: 'green',
    fellowship: 'pink',
    outreach: 'orange',
    other: 'grey'
  }
  return colors[type] || 'blue'
}

function getEventTypeClass(type) {
  return `border-left-${type || 'meeting'}`
}

function formatDate(dateString) {
  if (!dateString) return 'N/A'
  return qdate.formatDate(dateString, 'MMM DD, YYYY')
}

function formatDateTime(dateString) {
  if (!dateString) return 'N/A'
  return qdate.formatDate(dateString, 'MMM DD, YYYY h:mm A')
}

function getTimeUntilEvent(event) {
  if (!event.start_date) return null
  
  const now = new Date()
  const eventDate = new Date(event.start_date)
  const diffMs = eventDate - now
  
  // Don't show for past events
  if (diffMs < 0) return null
  
  const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24))
  const diffHours = Math.floor((diffMs % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60))
  
  if (diffDays === 0 && diffHours === 0) {
    return 'Starting soon!'
  } else if (diffDays === 0) {
    return `Starts in ${diffHours} hour${diffHours !== 1 ? 's' : ''}`
  } else if (diffDays === 1) {
    return 'Starts tomorrow'
  } else if (diffDays <= 7) {
    return `Starts in ${diffDays} days`
  }
  
  return null
}

// Lifecycle
onMounted(() => {
  fetchEvents()
})
</script>

<style scoped>
.event-card {
  transition: all 0.3s;
  height: 100%;
}

.event-card:hover {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  transform: translateY(-2px);
}

.border-left-meeting {
  border-left: 4px solid #2196F3;
}

.border-left-service {
  border-left: 4px solid #9C27B0;
}

.border-left-conference {
  border-left: 4px solid #FF5722;
}

.border-left-workshop {
  border-left: 4px solid #009688;
}

.border-left-prayer_meeting {
  border-left: 4px solid #3F51B5;
}

.border-left-bible_study {
  border-left: 4px solid #4CAF50;
}

.border-left-fellowship {
  border-left: 4px solid #E91E63;
}

.border-left-outreach {
  border-left: 4px solid #FF9800;
}

.border-left-other {
  border-left: 4px solid #757575;
}
</style>
