<template>
  <q-page class="q-pa-md">
    <!-- Header -->
    <div class="row items-center justify-between q-mb-md">
      <div class="text-h4">Ministries</div>
      <q-btn
        v-if="canCreateMinistry"
        color="primary"
        icon="add"
        label="New Ministry"
        @click="showCreateDialog = true"
      />
    </div>

    <!-- Search & Filters -->
    <q-card class="q-mb-md">
      <q-card-section>
        <div class="row q-col-gutter-md">
          <div class="col-12 col-md-6">
            <q-input
              v-model="search"
              outlined
              dense
              placeholder="Search ministries..."
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
              v-model="filters.status"
              outlined
              dense
              label="Status"
              :options="statusOptions"
              emit-value
              map-options
              clearable
              @update:model-value="fetchMinistries"
            />
          </div>
        </div>
      </q-card-section>
    </q-card>

    <!-- Ministries List -->
    <div v-if="loading" class="text-center q-py-xl">
      <q-spinner color="primary" size="50px" />
    </div>

    <div v-else-if="ministries.length === 0" class="text-center q-py-xl">
      <q-icon name="groups" size="64px" class="text-grey-5 q-mb-md" />
      <div class="text-h6 text-grey-6">No ministries found</div>
      <div class="text-grey-5">
        {{ search ? 'Try adjusting your search criteria' : 'Create your first ministry to get started' }}
      </div>
    </div>

    <div v-else class="row q-col-gutter-md">
      <div
        v-for="ministry in ministries"
        :key="ministry.id"
        class="col-12 col-md-6 col-lg-4"
      >
        <q-card class="ministry-card" :class="{ 'inactive-card': !ministry.is_active }">
          <q-card-section>
            <div class="row items-start justify-between">
              <div class="col">
                <div class="row items-center q-mb-sm">
                  <q-chip
                    :color="ministry.is_active ? 'positive' : 'grey'"
                    text-color="white"
                    size="sm"
                  >
                    <q-icon 
                      :name="ministry.is_active ? 'check_circle' : 'cancel'" 
                      size="16px" 
                      class="q-mr-xs" 
                    />
                    {{ ministry.is_active ? 'Active' : 'Inactive' }}
                  </q-chip>
                </div>
                
                <div class="text-h6 q-mb-sm">{{ ministry.name }}</div>
                
                <div class="text-body2 text-grey-7 q-mb-md" style="white-space: pre-line">
                  {{ ministry.description || 'No description provided' }}
                </div>

                <!-- Ministry Details -->
                <div class="q-mt-md">
                  <div v-if="ministry.leader" class="text-caption text-grey-8 q-mb-xs">
                    <q-icon name="person" size="16px" class="q-mr-xs" color="primary" />
                    <strong>Leader:</strong> {{ ministry.leader.name }}
                  </div>
                  <div v-if="ministry.contact_email" class="text-caption text-grey-8 q-mb-xs">
                    <q-icon name="email" size="16px" class="q-mr-xs" color="primary" />
                    <strong>Email:</strong> {{ ministry.contact_email }}
                  </div>
                  <div v-if="ministry.contact_phone" class="text-caption text-grey-8 q-mb-xs">
                    <q-icon name="phone" size="16px" class="q-mr-xs" color="primary" />
                    <strong>Phone:</strong> {{ ministry.contact_phone }}
                  </div>
                  <div v-if="ministry.members_count !== undefined" class="text-caption text-grey-8">
                    <q-icon name="groups" size="16px" class="q-mr-xs" color="primary" />
                    <strong>Members:</strong> {{ ministry.members_count || 0 }}
                  </div>
                </div>

                <div class="text-caption text-grey-6 q-mt-sm">
                  Created {{ formatDate(ministry.created_at) }}
                </div>
              </div>
              
              <div class="col-auto q-pl-md">
                <q-btn-dropdown
                  flat
                  round
                  dense
                  icon="more_vert"
                  dropdown-icon="none"
                >
                  <q-list>
                    <q-item
                      clickable
                      v-close-popup
                      @click="viewMembers(ministry)"
                    >
                      <q-item-section avatar>
                        <q-icon name="groups" />
                      </q-item-section>
                      <q-item-section>View Members</q-item-section>
                    </q-item>
                    <q-item
                      v-if="canManageMinistry(ministry)"
                      clickable
                      v-close-popup
                      @click="viewContributions(ministry)"
                    >
                      <q-item-section avatar>
                        <q-icon name="payments" />
                      </q-item-section>
                      <q-item-section>View Contributions</q-item-section>
                    </q-item>
                    <q-separator v-if="canManageMinistry(ministry)" />
                    <q-item
                      v-if="canManageMinistry(ministry)"
                      clickable
                      v-close-popup
                      @click="editMinistry(ministry)"
                    >
                      <q-item-section avatar>
                        <q-icon name="edit" />
                      </q-item-section>
                      <q-item-section>Edit</q-item-section>
                    </q-item>
                    <q-item
                      v-if="canManageMinistry(ministry)"
                      clickable
                      v-close-popup
                      @click="toggleMinistryStatus(ministry)"
                    >
                      <q-item-section avatar>
                        <q-icon :name="ministry.is_active ? 'block' : 'check_circle'" />
                      </q-item-section>
                      <q-item-section>
                        {{ ministry.is_active ? 'Deactivate' : 'Activate' }}
                      </q-item-section>
                    </q-item>
                    <q-separator v-if="canManageMinistry(ministry)" />
                    <q-item
                      v-if="canManageMinistry(ministry)"
                      clickable
                      v-close-popup
                      @click="deleteMinistry(ministry)"
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
        @update:model-value="fetchMinistries"
      />
    </div>

    <!-- Create/Edit Dialog -->
    <q-dialog v-model="showCreateDialog" persistent>
      <q-card style="min-width: 600px; max-width: 800px">
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6">{{ editingMinistry ? 'Edit' : 'Create' }} Ministry</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup @click="resetForm" />
        </q-card-section>

        <q-card-section>
          <q-form @submit="saveMinistry" class="q-gutter-md">
            <q-input
              v-model="form.name"
              outlined
              label="Ministry Name *"
              :rules="[val => !!val || 'Name is required']"
            />

            <q-input
              v-model="form.description"
              outlined
              type="textarea"
              label="Description"
              rows="4"
              hint="Brief description of the ministry's purpose and activities"
            />

            <q-select
              v-model="form.leader_id"
              outlined
              label="Ministry Leader *"
              :options="userOptions"
              option-value="id"
              option-label="name"
              emit-value
              map-options
              use-input
              input-debounce="300"
              @filter="filterUsers"
              :rules="[val => !!val || 'Leader is required']"
            >
              <template v-slot:no-option>
                <q-item>
                  <q-item-section class="text-grey">
                    No users found
                  </q-item-section>
                </q-item>
              </template>
              <template v-slot:prepend>
                <q-icon name="person" />
              </template>
            </q-select>

            <div class="row q-col-gutter-md">
              <div class="col-12 col-sm-6">
                <q-input
                  v-model="form.contact_email"
                  outlined
                  label="Contact Email"
                  type="email"
                  hint="Ministry contact email"
                >
                  <template v-slot:prepend>
                    <q-icon name="email" />
                  </template>
                </q-input>
              </div>
              <div class="col-12 col-sm-6">
                <q-input
                  v-model="form.contact_phone"
                  outlined
                  label="Contact Phone"
                  hint="Ministry contact phone"
                >
                  <template v-slot:prepend>
                    <q-icon name="phone" />
                  </template>
                </q-input>
              </div>
            </div>

            <q-toggle
              v-model="form.is_active"
              label="Active Ministry"
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

    <!-- Members Dialog -->
    <q-dialog v-model="showMembersDialog">
      <q-card style="min-width: 700px; max-width: 900px">
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6">
            {{ selectedMinistry?.name }} - Members
          </div>
          <q-space />
          <q-btn
            v-if="canManageMinistry(selectedMinistry)"
            flat
            icon="person_add"
            label="Add Member"
            color="primary"
            @click="showAddMemberDialog = true"
          />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>

        <q-card-section>
          <div v-if="loadingMembers" class="text-center q-py-lg">
            <q-spinner color="primary" size="40px" />
          </div>

          <div v-else-if="members.length === 0" class="text-center q-py-lg">
            <q-icon name="people_outline" size="48px" class="text-grey-5 q-mb-md" />
            <div class="text-body1 text-grey-6">No members yet</div>
          </div>

          <q-list v-else bordered separator>
            <q-item v-for="member in members" :key="member.id">
              <q-item-section avatar>
                <q-avatar color="primary" text-color="white">
                  {{ member.name?.charAt(0) || 'M' }}
                </q-avatar>
              </q-item-section>

              <q-item-section>
                <q-item-label>{{ member.name || 'Unknown' }}</q-item-label>
                <q-item-label caption>
                  <q-badge :color="getRoleColor(member.pivot?.role)" class="q-mr-sm">
                    {{ member.pivot?.role || 'member' }}
                  </q-badge>
                  Joined {{ formatDate(member.pivot?.joined_date) }}
                </q-item-label>
              </q-item-section>

              <q-item-section side>
                <q-chip
                  :color="member.pivot?.is_active ? 'positive' : 'grey'"
                  text-color="white"
                  size="sm"
                >
                  {{ member.pivot?.is_active ? 'Active' : 'Inactive' }}
                </q-chip>
              </q-item-section>

              <q-item-section side v-if="canManageMinistry(selectedMinistry)">
                <q-btn
                  flat
                  dense
                  round
                  icon="delete"
                  color="negative"
                  @click="removeMember(member)"
                >
                  <q-tooltip>Remove member</q-tooltip>
                </q-btn>
              </q-item-section>
            </q-item>
          </q-list>
        </q-card-section>
      </q-card>
    </q-dialog>

    <!-- Add Member Dialog -->
    <q-dialog v-model="showAddMemberDialog" persistent>
      <q-card style="min-width: 500px">
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6">Add Member</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup @click="resetMemberForm" />
        </q-card-section>

        <q-card-section>
          <q-form @submit="addMember" class="q-gutter-md">
            <q-select
              v-model="memberForm.user_id"
              outlined
              label="Select Member *"
              :options="availableUserOptions"
              option-value="id"
              option-label="name"
              emit-value
              map-options
              use-input
              input-debounce="300"
              @filter="filterAvailableUsers"
              :rules="[val => !!val || 'Member is required']"
            >
              <template v-slot:no-option>
                <q-item>
                  <q-item-section class="text-grey">
                    No users found
                  </q-item-section>
                </q-item>
              </template>
            </q-select>

            <q-select
              v-model="memberForm.role"
              outlined
              label="Role *"
              :options="roleOptions"
              emit-value
              map-options
              :rules="[val => !!val || 'Role is required']"
            />

            <q-input
              v-model="memberForm.joined_date"
              outlined
              label="Joined Date *"
              :rules="[val => !!val || 'Joined date is required']"
            >
              <template v-slot:prepend>
                <q-icon name="event" class="cursor-pointer">
                  <q-popup-proxy cover transition-show="scale" transition-hide="scale">
                    <q-date v-model="memberForm.joined_date" mask="YYYY-MM-DD">
                      <div class="row items-center justify-end">
                        <q-btn v-close-popup label="Close" color="primary" flat />
                      </div>
                    </q-date>
                  </q-popup-proxy>
                </q-icon>
              </template>
            </q-input>

            <q-toggle
              v-model="memberForm.is_active"
              label="Active Member"
              color="positive"
            />

            <div class="row q-gutter-sm justify-end">
              <q-btn label="Cancel" flat v-close-popup @click="resetMemberForm" />
              <q-btn
                type="submit"
                label="Add Member"
                color="primary"
                :loading="addingMember"
              />
            </div>
          </q-form>
        </q-card-section>
      </q-card>
    </q-dialog>

    <!-- Contributions Dialog -->
    <q-dialog v-model="showContributionsDialog">
      <q-card style="min-width: 700px; max-width: 900px">
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6">
            {{ selectedMinistry?.name }} - Contributions
          </div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>

        <q-card-section>
          <div v-if="loadingContributions" class="text-center q-py-lg">
            <q-spinner color="primary" size="40px" />
          </div>

          <div v-else-if="contributions.length === 0" class="text-center q-py-lg">
            <q-icon name="payments" size="48px" class="text-grey-5 q-mb-md" />
            <div class="text-body1 text-grey-6">No contributions yet</div>
          </div>

          <div v-else>
            <!-- Summary Card -->
            <q-card flat bordered class="q-mb-md bg-primary text-white">
              <q-card-section>
                <div class="text-h6">Total Contributions</div>
                <div class="text-h4">${{ totalContributions.toFixed(2) }}</div>
              </q-card-section>
            </q-card>

            <q-list bordered separator>
              <q-item v-for="contribution in contributions" :key="contribution.id">
                <q-item-section avatar>
                  <q-avatar color="green" text-color="white">
                    <q-icon name="payments" />
                  </q-avatar>
                </q-item-section>

                <q-item-section>
                  <q-item-label>{{ contribution.user?.name || 'Anonymous' }}</q-item-label>
                  <q-item-label caption>
                    {{ formatDate(contribution.contribution_date) }}
                    <span v-if="contribution.notes"> • {{ contribution.notes }}</span>
                  </q-item-label>
                </q-item-section>

                <q-item-section side>
                  <div class="text-h6 text-positive">${{ contribution.amount }}</div>
                  <div class="text-caption text-grey-7">{{ contribution.payment_method }}</div>
                </q-item-section>
              </q-item>
            </q-list>
          </div>
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
const ministries = ref([])
const members = ref([])
const contributions = ref([])
const loading = ref(false)
const loadingMembers = ref(false)
const loadingContributions = ref(false)
const saving = ref(false)
const addingMember = ref(false)
const search = ref('')
const filters = ref({
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
const showMembersDialog = ref(false)
const showAddMemberDialog = ref(false)
const showContributionsDialog = ref(false)
const editingMinistry = ref(null)
const selectedMinistry = ref(null)
const form = ref({
  name: '',
  description: '',
  leader_id: null,
  contact_email: '',
  contact_phone: '',
  is_active: true
})
const memberForm = ref({
  user_id: null,
  role: 'member',
  joined_date: qdate.formatDate(new Date(), 'YYYY-MM-DD'),
  is_active: true
})

// Options
const userOptions = ref([])
const allUsers = ref([])
const availableUserOptions = ref([])

const statusOptions = [
  { label: 'Active', value: 'active' },
  { label: 'Inactive', value: 'inactive' }
]

const roleOptions = [
  { label: 'Member', value: 'member' },
  { label: 'Leader', value: 'leader' },
  { label: 'Co-Leader', value: 'co-leader' },
  { label: 'Secretary', value: 'secretary' }
]

// Computed
const canCreateMinistry = computed(() => {
  return authStore.hasAnyRole(['pastor', 'administrator'])
})

const totalContributions = computed(() => {
  return contributions.value.reduce((sum, contrib) => sum + parseFloat(contrib.amount || 0), 0)
})

// Methods
function canManageMinistry(ministry) {
  if (!ministry) return false
  if (authStore.hasRole('administrator')) return true
  if (authStore.hasRole('pastor')) return true
  return ministry.leader_id === authStore.user?.id
}

async function fetchMinistries() {
  loading.value = true
  try {
    const params = {
      page: pagination.value.current_page,
      per_page: pagination.value.per_page
    }

    if (search.value) {
      params.search = search.value
    }
    if (filters.value.status) {
      params.is_active = filters.value.status === 'active' ? 1 : 0
    }

    const response = await api.get('/ministries', { params })
    
    // Handle both paginated and non-paginated responses
    if (response.data.data && Array.isArray(response.data.data)) {
      ministries.value = response.data.data
      pagination.value = {
        current_page: response.data.current_page || 1,
        last_page: response.data.last_page || 1,
        per_page: response.data.per_page || 15,
        total: response.data.total || response.data.data.length
      }
    } else if (Array.isArray(response.data)) {
      ministries.value = response.data
      pagination.value = {
        current_page: 1,
        last_page: 1,
        per_page: 15,
        total: response.data.length
      }
    } else {
      ministries.value = []
      pagination.value = {
        current_page: 1,
        last_page: 1,
        per_page: 15,
        total: 0
      }
    }
  } catch (error) {
    ministries.value = []
    $q.notify({
      type: 'negative',
      message: 'Failed to load ministries',
      caption: error.response?.data?.message || error.message
    })
  } finally {
    loading.value = false
  }
}

async function fetchUsers() {
  try {
    const response = await api.get('/users')
    allUsers.value = Array.isArray(response.data) ? response.data : response.data.data || []
    userOptions.value = allUsers.value
    availableUserOptions.value = allUsers.value
  } catch (error) {
    console.error('Failed to fetch users:', error)
  }
}

let searchTimeout = null
function debouncedSearch() {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    pagination.value.current_page = 1
    fetchMinistries()
  }, 500)
}

function clearSearch() {
  search.value = ''
  fetchMinistries()
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

function filterAvailableUsers(val, update) {
  update(() => {
    if (val === '') {
      availableUserOptions.value = allUsers.value
    } else {
      const needle = val.toLowerCase()
      availableUserOptions.value = allUsers.value.filter(
        user => user.name.toLowerCase().indexOf(needle) > -1 ||
                (user.email && user.email.toLowerCase().indexOf(needle) > -1)
      )
    }
  })
}

function editMinistry(ministry) {
  editingMinistry.value = ministry
  form.value = {
    name: ministry.name,
    description: ministry.description || '',
    leader_id: ministry.leader_id,
    contact_email: ministry.contact_email || '',
    contact_phone: ministry.contact_phone || '',
    is_active: ministry.is_active
  }
  showCreateDialog.value = true
}

async function saveMinistry() {
  saving.value = true
  try {
    if (editingMinistry.value) {
      await api.put(`/ministries/${editingMinistry.value.id}`, form.value)
      $q.notify({
        type: 'positive',
        message: 'Ministry updated successfully'
      })
    } else {
      await api.post('/ministries', form.value)
      $q.notify({
        type: 'positive',
        message: 'Ministry created successfully'
      })
    }
    
    showCreateDialog.value = false
    resetForm()
    fetchMinistries()
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: 'Failed to save ministry',
      caption: error.response?.data?.message || error.message
    })
  } finally {
    saving.value = false
  }
}

async function toggleMinistryStatus(ministry) {
  try {
    await api.put(`/ministries/${ministry.id}`, {
      is_active: !ministry.is_active
    })
    $q.notify({
      type: 'positive',
      message: `Ministry ${ministry.is_active ? 'deactivated' : 'activated'} successfully`
    })
    fetchMinistries()
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: 'Failed to update ministry status',
      caption: error.response?.data?.message || error.message
    })
  }
}

function deleteMinistry(ministry) {
  $q.dialog({
    title: 'Confirm Delete',
    message: `Are you sure you want to delete "${ministry.name}"? This action cannot be undone.`,
    cancel: true,
    persistent: true
  }).onOk(async () => {
    try {
      await api.delete(`/ministries/${ministry.id}`)
      $q.notify({
        type: 'positive',
        message: 'Ministry deleted successfully'
      })
      fetchMinistries()
    } catch (error) {
      $q.notify({
        type: 'negative',
        message: 'Failed to delete ministry',
        caption: error.response?.data?.message || error.message
      })
    }
  })
}

async function viewMembers(ministry) {
  selectedMinistry.value = ministry
  showMembersDialog.value = true
  loadingMembers.value = true
  
  try {
    const response = await api.get(`/ministries/${ministry.id}/members`)
    members.value = Array.isArray(response.data) ? response.data : response.data.data || []
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: 'Failed to load members',
      caption: error.response?.data?.message || error.message
    })
    members.value = []
  } finally {
    loadingMembers.value = false
  }
}

async function addMember() {
  addingMember.value = true
  try {
    await api.post(`/ministries/${selectedMinistry.value.id}/members`, memberForm.value)
    $q.notify({
      type: 'positive',
      message: 'Member added successfully'
    })
    showAddMemberDialog.value = false
    resetMemberForm()
    viewMembers(selectedMinistry.value) // Refresh members list
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: 'Failed to add member',
      caption: error.response?.data?.message || error.message
    })
  } finally {
    addingMember.value = false
  }
}

function removeMember(member) {
  $q.dialog({
    title: 'Confirm Remove',
    message: `Are you sure you want to remove ${member.name} from this ministry?`,
    cancel: true,
    persistent: true
  }).onOk(async () => {
    try {
      await api.delete(`/ministries/${selectedMinistry.value.id}/members/${member.id}`)
      $q.notify({
        type: 'positive',
        message: 'Member removed successfully'
      })
      viewMembers(selectedMinistry.value) // Refresh members list
    } catch (error) {
      $q.notify({
        type: 'negative',
        message: 'Failed to remove member',
        caption: error.response?.data?.message || error.message
      })
    }
  })
}

async function viewContributions(ministry) {
  selectedMinistry.value = ministry
  showContributionsDialog.value = true
  loadingContributions.value = true
  
  try {
    const response = await api.get(`/ministries/${ministry.id}/contributions`)
    contributions.value = Array.isArray(response.data) ? response.data : response.data.data || []
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: 'Failed to load contributions',
      caption: error.response?.data?.message || error.message
    })
    contributions.value = []
  } finally {
    loadingContributions.value = false
  }
}

function resetForm() {
  editingMinistry.value = null
  form.value = {
    name: '',
    description: '',
    leader_id: null,
    contact_email: '',
    contact_phone: '',
    is_active: true
  }
}

function resetMemberForm() {
  memberForm.value = {
    user_id: null,
    role: 'member',
    joined_date: qdate.formatDate(new Date(), 'YYYY-MM-DD'),
    is_active: true
  }
}

function getRoleColor(role) {
  const colors = {
    leader: 'primary',
    'co-leader': 'secondary',
    secretary: 'info',
    member: 'grey'
  }
  return colors[role] || 'grey'
}

function formatDate(dateString) {
  if (!dateString) return 'N/A'
  return qdate.formatDate(dateString, 'MMM DD, YYYY')
}

// Lifecycle
onMounted(() => {
  fetchMinistries()
  fetchUsers()
})
</script>

<style scoped>
.ministry-card {
  transition: all 0.3s;
  height: 100%;
  border-left: 4px solid #4CAF50;
}

.ministry-card:hover {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  transform: translateY(-2px);
}

.inactive-card {
  border-left-color: #9E9E9E;
  opacity: 0.8;
}
</style>
