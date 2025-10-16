<template>
  <q-page class="q-pa-md">
    <!-- Header -->
    <div class="row items-center justify-between q-mb-md">
      <div class="text-h4">Messages</div>
      <q-btn
        color="primary"
        icon="add"
        label="Compose Message"
        @click="showComposeDialog = true"
      />
    </div>

    <!-- Tabs -->
    <q-tabs
      v-model="activeTab"
      dense
      class="text-grey"
      active-color="primary"
      indicator-color="primary"
      align="left"
      narrow-indicator
      @update:model-value="onTabChange"
    >
      <q-tab name="inbox" label="Inbox" icon="inbox">
        <q-badge v-if="unreadCount > 0" color="red" floating>{{ unreadCount }}</q-badge>
      </q-tab>
      <q-tab name="sent" label="Sent" icon="send" />
      <q-tab name="all" label="All Messages" icon="mail" />
    </q-tabs>

    <q-separator class="q-mb-md" />

    <!-- Search -->
    <q-card class="q-mb-md">
      <q-card-section>
        <div class="row q-col-gutter-md">
          <div class="col-12 col-md-6">
            <q-input
              v-model="search"
              outlined
              dense
              placeholder="Search messages..."
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
          <div class="col-12 col-md-3">
            <q-select
              v-model="filters.messageType"
              outlined
              dense
              label="Message Type"
              :options="messageTypeOptions"
              emit-value
              map-options
              clearable
              @update:model-value="fetchMessages"
            />
          </div>
          <div class="col-12 col-md-3">
            <q-select
              v-model="filters.readStatus"
              outlined
              dense
              label="Status"
              :options="readStatusOptions"
              emit-value
              map-options
              clearable
              @update:model-value="fetchMessages"
            />
          </div>
        </div>
      </q-card-section>
    </q-card>

    <!-- Messages List -->
    <div v-if="loading" class="text-center q-py-xl">
      <q-spinner color="primary" size="50px" />
    </div>

    <div v-else-if="messages.length === 0" class="text-center q-py-xl">
      <q-icon name="mail_outline" size="64px" class="text-grey-5 q-mb-md" />
      <div class="text-h6 text-grey-6">No messages found</div>
      <div class="text-grey-5">
        {{ search ? 'Try adjusting your search criteria' : getEmptyMessage() }}
      </div>
    </div>

    <div v-else>
      <q-list bordered separator class="rounded-borders">
        <q-item
          v-for="message in messages"
          :key="message.id"
          clickable
          @click="viewMessage(message)"
          :class="{ 'bg-blue-1': !message.is_read && activeTab === 'inbox' }"
        >
          <q-item-section avatar>
            <q-avatar :color="getMessageTypeColor(message.message_type)" text-color="white">
              <q-icon :name="getMessageTypeIcon(message.message_type)" />
            </q-avatar>
          </q-item-section>

          <q-item-section>
            <q-item-label class="row items-center">
              <span class="text-weight-medium" :class="{ 'text-weight-bold': !message.is_read && activeTab === 'inbox' }">
                {{ message.subject }}
              </span>
              <q-chip
                v-if="!message.is_read && activeTab === 'inbox'"
                size="sm"
                color="primary"
                text-color="white"
                class="q-ml-sm"
              >
                NEW
              </q-chip>
              <q-chip
                size="sm"
                :color="getMessageTypeColor(message.message_type)"
                text-color="white"
                class="q-ml-sm"
              >
                {{ message.message_type }}
              </q-chip>
            </q-item-label>
            
            <q-item-label caption lines="2">
              {{ message.content }}
            </q-item-label>
            
            <q-item-label caption class="q-mt-xs">
              <q-icon name="person" size="14px" class="q-mr-xs" />
              {{ getSenderName(message) }}
              <span class="q-mx-sm">•</span>
              <q-icon name="schedule" size="14px" class="q-mr-xs" />
              {{ formatDate(message.created_at) }}
            </q-item-label>
          </q-item-section>

          <q-item-section side>
            <div class="row items-center q-gutter-sm">
              <q-btn
                v-if="activeTab === 'inbox' && !message.is_read"
                flat
                dense
                round
                icon="mark_email_read"
                color="primary"
                @click.stop="markAsRead(message)"
              >
                <q-tooltip>Mark as read</q-tooltip>
              </q-btn>
              <q-btn
                flat
                dense
                round
                icon="delete"
                color="negative"
                @click.stop="deleteMessage(message)"
              >
                <q-tooltip>Delete</q-tooltip>
              </q-btn>
            </div>
          </q-item-section>
        </q-item>
      </q-list>

      <!-- Pagination -->
      <div v-if="pagination.last_page > 1" class="flex flex-center q-mt-md">
        <q-pagination
          v-model="pagination.current_page"
          :max="pagination.last_page"
          :max-pages="6"
          boundary-numbers
          @update:model-value="fetchMessages"
        />
      </div>
    </div>

    <!-- Compose Message Dialog -->
    <q-dialog v-model="showComposeDialog" persistent>
      <q-card style="min-width: 600px; max-width: 800px">
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6">Compose Message</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup @click="resetComposeForm" />
        </q-card-section>

        <q-card-section>
          <q-form @submit="sendMessage" class="q-gutter-md">
            <q-select
              v-model="composeForm.message_type"
              outlined
              label="Message Type *"
              :options="composeMessageTypeOptions"
              emit-value
              map-options
              :rules="[val => !!val || 'Message type is required']"
              @update:model-value="onMessageTypeChange"
            />

            <!-- Personal Message - Select Recipient -->
            <q-select
              v-if="composeForm.message_type === 'personal'"
              v-model="composeForm.recipient_id"
              outlined
              label="Recipient *"
              :options="userOptions"
              option-value="id"
              option-label="name"
              emit-value
              map-options
              use-input
              input-debounce="300"
              @filter="filterUsers"
              :rules="[val => !!val || 'Recipient is required']"
            >
              <template v-slot:no-option>
                <q-item>
                  <q-item-section class="text-grey">
                    No users found
                  </q-item-section>
                </q-item>
              </template>
            </q-select>

            <!-- Ministry Message - Select Ministry -->
            <q-select
              v-if="composeForm.message_type === 'ministry'"
              v-model="composeForm.ministry_id"
              outlined
              label="Ministry *"
              :options="ministryOptions"
              option-value="id"
              option-label="name"
              emit-value
              map-options
              :rules="[val => !!val || 'Ministry is required']"
            />

            <!-- Broadcast Message - Info -->
            <q-banner
              v-if="composeForm.message_type === 'broadcast'"
              dense
              class="bg-warning text-white"
            >
              <template v-slot:avatar>
                <q-icon name="campaign" />
              </template>
              This message will be sent to all church members
            </q-banner>

            <q-input
              v-model="composeForm.subject"
              outlined
              label="Subject *"
              :rules="[val => !!val || 'Subject is required']"
            />

            <q-input
              v-model="composeForm.content"
              outlined
              type="textarea"
              label="Message *"
              rows="6"
              :rules="[val => !!val || 'Message content is required']"
            />

            <div class="row q-gutter-sm justify-end">
              <q-btn label="Cancel" flat v-close-popup @click="resetComposeForm" />
              <q-btn
                type="submit"
                label="Send Message"
                color="primary"
                icon="send"
                :loading="sending"
              />
            </div>
          </q-form>
        </q-card-section>
      </q-card>
    </q-dialog>

    <!-- View Message Dialog -->
    <q-dialog v-model="showViewDialog">
      <q-card style="min-width: 600px; max-width: 800px">
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6">Message Details</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>

        <q-card-section v-if="selectedMessage">
          <div class="q-mb-md">
            <div class="row items-center q-mb-sm">
              <q-chip
                :color="getMessageTypeColor(selectedMessage.message_type)"
                text-color="white"
                size="sm"
              >
                {{ selectedMessage.message_type }}
              </q-chip>
              <q-chip
                v-if="!selectedMessage.is_read"
                color="primary"
                text-color="white"
                size="sm"
              >
                UNREAD
              </q-chip>
            </div>
            <div class="text-h6 q-mb-sm">{{ selectedMessage.subject }}</div>
            <div class="text-caption text-grey-7">
              <q-icon name="person" size="16px" class="q-mr-xs" />
              From: {{ getSenderName(selectedMessage) }}
              <span class="q-mx-sm">•</span>
              <q-icon name="schedule" size="16px" class="q-mr-xs" />
              {{ formatDateTime(selectedMessage.created_at) }}
            </div>
            <div v-if="activeTab === 'inbox' && selectedMessage.recipient" class="text-caption text-grey-7">
              <q-icon name="mail" size="16px" class="q-mr-xs" />
              To: {{ selectedMessage.recipient.name }}
            </div>
          </div>

          <q-separator class="q-mb-md" />

          <div class="text-body1" style="white-space: pre-line">
            {{ selectedMessage.content }}
          </div>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn
            v-if="activeTab === 'inbox' && selectedMessage && !selectedMessage.is_read"
            flat
            label="Mark as Read"
            color="primary"
            icon="mark_email_read"
            @click="markAsRead(selectedMessage)"
          />
          <q-btn
            flat
            label="Delete"
            color="negative"
            icon="delete"
            @click="deleteMessage(selectedMessage)"
          />
          <q-btn flat label="Close" color="primary" v-close-popup />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useQuasar, date } from 'quasar'
import { api } from 'src/services/api'
// import { useAuthStore } from 'src/stores/auth'

const $q = useQuasar()
// const authStore = useAuthStore()

// Data
const messages = ref([])
const loading = ref(false)
const sending = ref(false)
const search = ref('')
const activeTab = ref('inbox')
const unreadCount = ref(0)
const filters = ref({
  messageType: null,
  readStatus: null
})
const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0
})

// Dialog & Forms
const showComposeDialog = ref(false)
const showViewDialog = ref(false)
const selectedMessage = ref(null)
const composeForm = ref({
  message_type: 'personal',
  recipient_id: null,
  ministry_id: null,
  subject: '',
  content: ''
})

// Options
const userOptions = ref([])
const allUsers = ref([])
const ministryOptions = ref([])

const messageTypeOptions = [
  { label: 'Personal', value: 'personal' },
  { label: 'Ministry', value: 'ministry' },
  { label: 'Broadcast', value: 'broadcast' }
]

const composeMessageTypeOptions = [
  { label: 'Personal Message', value: 'personal' },
  { label: 'Ministry Message', value: 'ministry' },
  { label: 'Broadcast Message', value: 'broadcast' }
]

const readStatusOptions = [
  { label: 'Unread', value: 'unread' },
  { label: 'Read', value: 'read' }
]

// Methods
function getEmptyMessage() {
  if (activeTab.value === 'inbox') return 'Your inbox is empty'
  if (activeTab.value === 'sent') return 'No sent messages'
  return 'No messages available'
}

function getMessageTypeIcon(type) {
  const icons = {
    personal: 'person',
    ministry: 'groups',
    broadcast: 'campaign'
  }
  return icons[type] || 'mail'
}

function getMessageTypeColor(type) {
  const colors = {
    personal: 'blue',
    ministry: 'purple',
    broadcast: 'orange'
  }
  return colors[type] || 'grey'
}

function getSenderName(message) {
  if (activeTab.value === 'sent') {
    // Show recipient for sent messages
    if (message.recipient) return `To: ${message.recipient.name}`
    if (message.ministry) return `To: ${message.ministry.name} Ministry`
    if (message.message_type === 'broadcast') return 'To: All Members'
    return 'Unknown'
  } else {
    // Show sender for inbox/all messages
    return message.sender?.name || 'Unknown Sender'
  }
}

async function fetchMessages() {
  loading.value = true
  try {
    let endpoint = '/messages'
    
    if (activeTab.value === 'inbox') {
      endpoint = '/messages/inbox'
    } else if (activeTab.value === 'sent') {
      endpoint = '/messages/sent'
    }

    const params = {
      page: pagination.value.current_page,
      per_page: pagination.value.per_page
    }

    if (search.value) {
      params.search = search.value
    }
    if (filters.value.messageType) {
      params.message_type = filters.value.messageType
    }
    if (filters.value.readStatus) {
      params.is_read = filters.value.readStatus === 'read' ? 1 : 0
    }

    const response = await api.get(endpoint, { params })
    
    // Handle both paginated and non-paginated responses
    if (response.data.data && Array.isArray(response.data.data)) {
      messages.value = response.data.data
      pagination.value = {
        current_page: response.data.current_page || 1,
        last_page: response.data.last_page || 1,
        per_page: response.data.per_page || 15,
        total: response.data.total || response.data.data.length
      }
    } else if (Array.isArray(response.data)) {
      messages.value = response.data
      pagination.value = {
        current_page: 1,
        last_page: 1,
        per_page: 15,
        total: response.data.length
      }
    }

    // Update unread count
    if (activeTab.value === 'inbox') {
      updateUnreadCount()
    }
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: 'Failed to load messages',
      caption: error.response?.data?.message || error.message
    })
  } finally {
    loading.value = false
  }
}

async function updateUnreadCount() {
  try {
    const response = await api.get('/messages/inbox')
    const inboxMessages = Array.isArray(response.data) ? response.data : response.data.data
    unreadCount.value = inboxMessages.filter(m => !m.is_read).length
  } catch (error) {
    console.error('Failed to update unread count:', error)
  }
}

function onTabChange() {
  pagination.value.current_page = 1
  fetchMessages()
}

let searchTimeout = null
function debouncedSearch() {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    pagination.value.current_page = 1
    fetchMessages()
  }, 500)
}

function clearSearch() {
  search.value = ''
  fetchMessages()
}

async function fetchUsers() {
  try {
    const response = await api.get('/users')
    allUsers.value = Array.isArray(response.data) ? response.data : response.data.data || []
    userOptions.value = allUsers.value
  } catch (error) {
    console.error('Failed to fetch users:', error)
  }
}

async function fetchMinistries() {
  try {
    const response = await api.get('/ministries')
    ministryOptions.value = Array.isArray(response.data) ? response.data : response.data.data || []
  } catch (error) {
    console.error('Failed to fetch ministries:', error)
  }
}

function filterUsers(val, update) {
  update(() => {
    if (val === '') {
      userOptions.value = allUsers.value
    } else {
      const needle = val.toLowerCase()
      userOptions.value = allUsers.value.filter(
        user => user.name.toLowerCase().indexOf(needle) > -1 ||
                (user.email && user.email.toLowerCase().indexOf(needle) > -1)
      )
    }
  })
}

function onMessageTypeChange() {
  composeForm.value.recipient_id = null
  composeForm.value.ministry_id = null
}

async function sendMessage() {
  sending.value = true
  try {
    const payload = {
      subject: composeForm.value.subject,
      content: composeForm.value.content,
      message_type: composeForm.value.message_type
    }

    if (composeForm.value.message_type === 'personal') {
      payload.recipient_id = composeForm.value.recipient_id
    } else if (composeForm.value.message_type === 'ministry') {
      payload.ministry_id = composeForm.value.ministry_id
    }

    let endpoint = '/messages'
    if (composeForm.value.message_type === 'broadcast') {
      endpoint = '/messages/broadcast'
    }

    await api.post(endpoint, payload)
    
    $q.notify({
      type: 'positive',
      message: 'Message sent successfully',
      icon: 'send'
    })
    
    showComposeDialog.value = false
    resetComposeForm()
    
    // Refresh the current tab
    fetchMessages()
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: 'Failed to send message',
      caption: error.response?.data?.message || error.message
    })
  } finally {
    sending.value = false
  }
}

async function markAsRead(message) {
  try {
    await api.post(`/messages/${message.id}/read`)
    
    $q.notify({
      type: 'positive',
      message: 'Message marked as read',
      icon: 'mark_email_read'
    })
    
    // Update the message locally
    message.is_read = true
    
    // Update unread count
    updateUnreadCount()
    
    // Close view dialog if open
    if (showViewDialog.value && selectedMessage.value?.id === message.id) {
      selectedMessage.value.is_read = true
    }
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: 'Failed to mark message as read',
      caption: error.response?.data?.message || error.message
    })
  }
}

function viewMessage(message) {
  selectedMessage.value = message
  showViewDialog.value = true
  
  // Auto-mark as read if viewing inbox message
  if (activeTab.value === 'inbox' && !message.is_read) {
    markAsRead(message)
  }
}

function deleteMessage(message) {
  $q.dialog({
    title: 'Confirm Delete',
    message: `Are you sure you want to delete this message?`,
    cancel: true,
    persistent: true
  }).onOk(async () => {
    try {
      await api.delete(`/messages/${message.id}`)
      
      $q.notify({
        type: 'positive',
        message: 'Message deleted successfully',
        icon: 'delete'
      })
      
      // Close dialogs
      showViewDialog.value = false
      
      // Refresh messages
      fetchMessages()
    } catch (error) {
      $q.notify({
        type: 'negative',
        message: 'Failed to delete message',
        caption: error.response?.data?.message || error.message
      })
    }
  })
}

function resetComposeForm() {
  composeForm.value = {
    message_type: 'personal',
    recipient_id: null,
    ministry_id: null,
    subject: '',
    content: ''
  }
}

function formatDate(dateString) {
  if (!dateString) return 'N/A'
  const now = new Date()
  const messageDate = new Date(dateString)
  const diffMs = now - messageDate
  const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24))
  
  if (diffDays === 0) {
    return date.formatDate(dateString, 'h:mm A')
  } else if (diffDays === 1) {
    return 'Yesterday'
  } else if (diffDays < 7) {
    return date.formatDate(dateString, 'ddd')
  } else {
    return date.formatDate(dateString, 'MMM DD')
  }
}

function formatDateTime(dateString) {
  if (!dateString) return 'N/A'
  return date.formatDate(dateString, 'MMM DD, YYYY h:mm A')
}

// Lifecycle
onMounted(() => {
  fetchMessages()
  fetchUsers()
  fetchMinistries()
  updateUnreadCount()
})
</script>

<style scoped>
.q-item.bg-blue-1 {
  background-color: #e3f2fd;
}

.q-item:hover {
  background-color: #f5f5f5;
}

.q-item.bg-blue-1:hover {
  background-color: #bbdefb;
}
</style>
