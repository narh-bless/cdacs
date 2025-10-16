<template>
  <q-page class="q-pa-md">
    <!-- Header -->
    <div class="row items-center justify-between q-mb-md">
      <div class="text-h4">Contributions</div>
      <div class="row q-gutter-sm">
        <q-btn
          color="secondary"
          icon="assessment"
          label="Reports"
          @click="showReportsDialog = true"
        />
        <q-btn
          v-if="canRecordContribution"
          color="primary"
          icon="add"
          label="Record Contribution"
          @click="showCreateDialog = true"
        />
      </div>
    </div>

    <!-- Summary Cards -->
    <div class="row q-col-gutter-md q-mb-md">
      <div class="col-12 col-sm-6 col-md-3">
        <q-card class="summary-card bg-primary text-white">
          <q-card-section>
            <div class="text-caption">Total Contributions</div>
            <div class="text-h5">${{ formatCurrency(summary.total) }}</div>
          </q-card-section>
        </q-card>
      </div>
      <div class="col-12 col-sm-6 col-md-3">
        <q-card class="summary-card bg-green text-white">
          <q-card-section>
            <div class="text-caption">Total Tithes</div>
            <div class="text-h5">${{ formatCurrency(summary.tithe) }}</div>
          </q-card-section>
        </q-card>
      </div>
      <div class="col-12 col-sm-6 col-md-3">
        <q-card class="summary-card bg-orange text-white">
          <q-card-section>
            <div class="text-caption">Total Offerings</div>
            <div class="text-h5">${{ formatCurrency(summary.offering) }}</div>
          </q-card-section>
        </q-card>
      </div>
      <div class="col-12 col-sm-6 col-md-3">
        <q-card class="summary-card bg-purple text-white">
          <q-card-section>
            <div class="text-caption">Total Donations</div>
            <div class="text-h5">${{ formatCurrency(summary.donation) }}</div>
          </q-card-section>
        </q-card>
      </div>
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
              placeholder="Search by user, notes..."
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
          <div class="col-12 col-sm-6 col-md-2">
            <q-select
              v-model="filters.type"
              outlined
              dense
              label="Type"
              :options="typeOptions"
              emit-value
              map-options
              clearable
              @update:model-value="fetchContributions"
            />
          </div>
          <div class="col-12 col-sm-6 col-md-2">
            <q-select
              v-model="filters.paymentMethod"
              outlined
              dense
              label="Payment Method"
              :options="paymentMethodOptions"
              emit-value
              map-options
              clearable
              @update:model-value="fetchContributions"
            />
          </div>
          <div class="col-12 col-sm-6 col-md-2">
            <q-input
              v-model="filters.startDate"
              outlined
              dense
              label="Start Date"
              @update:model-value="fetchContributions"
            >
              <template v-slot:prepend>
                <q-icon name="event" class="cursor-pointer">
                  <q-popup-proxy cover transition-show="scale" transition-hide="scale">
                    <q-date v-model="filters.startDate" mask="YYYY-MM-DD">
                      <div class="row items-center justify-end">
                        <q-btn v-close-popup label="Close" color="primary" flat />
                      </div>
                    </q-date>
                  </q-popup-proxy>
                </q-icon>
              </template>
            </q-input>
          </div>
          <div class="col-12 col-sm-6 col-md-2">
            <q-input
              v-model="filters.endDate"
              outlined
              dense
              label="End Date"
              @update:model-value="fetchContributions"
            >
              <template v-slot:prepend>
                <q-icon name="event" class="cursor-pointer">
                  <q-popup-proxy cover transition-show="scale" transition-hide="scale">
                    <q-date v-model="filters.endDate" mask="YYYY-MM-DD">
                      <div class="row items-center justify-end">
                        <q-btn v-close-popup label="Close" color="primary" flat />
                      </div>
                    </q-date>
                  </q-popup-proxy>
                </q-icon>
              </template>
            </q-input>
          </div>
        </div>
      </q-card-section>
    </q-card>

    <!-- Contributions List -->
    <div v-if="loading" class="text-center q-py-xl">
      <q-spinner color="primary" size="50px" />
    </div>

    <div v-else-if="contributions.length === 0" class="text-center q-py-xl">
      <q-icon name="payments" size="64px" class="text-grey-5 q-mb-md" />
      <div class="text-h6 text-grey-6">No contributions found</div>
      <div class="text-grey-5">
        {{ search || filters.type || filters.paymentMethod ? 'Try adjusting your search criteria' : 'Record your first contribution to get started' }}
      </div>
    </div>

    <q-card v-else>
      <q-table
        :rows="contributions"
        :columns="columns"
        row-key="id"
        flat
        :rows-per-page-options="[10, 25, 50]"
        v-model:pagination="tablePagination"
      >
        <template v-slot:body-cell-user="props">
          <q-td :props="props">
            <div class="row items-center">
              <q-avatar color="primary" text-color="white" size="32px" class="q-mr-sm">
                {{ props.row.user?.name?.charAt(0) || 'U' }}
              </q-avatar>
              <div>
                <div class="text-weight-medium">{{ props.row.user?.name || 'Unknown' }}</div>
                <div class="text-caption text-grey-7">{{ props.row.user?.email }}</div>
              </div>
            </div>
          </q-td>
        </template>

        <template v-slot:body-cell-amount="props">
          <q-td :props="props">
            <div class="text-h6 text-positive">${{ parseFloat(props.row.amount).toFixed(2) }}</div>
          </q-td>
        </template>

        <template v-slot:body-cell-type="props">
          <q-td :props="props">
            <q-chip
              :color="getTypeColor(props.row.type)"
              text-color="white"
              size="sm"
            >
              <q-icon :name="getTypeIcon(props.row.type)" size="16px" class="q-mr-xs" />
              {{ capitalizeFirstLetter(props.row.type) }}
            </q-chip>
          </q-td>
        </template>

        <template v-slot:body-cell-payment_method="props">
          <q-td :props="props">
            <q-badge :color="getPaymentMethodColor(props.row.payment_method)">
              {{ formatPaymentMethod(props.row.payment_method) }}
            </q-badge>
          </q-td>
        </template>

        <template v-slot:body-cell-ministry="props">
          <q-td :props="props">
            <div v-if="props.row.ministry">
              <q-chip color="purple" text-color="white" size="sm">
                <q-icon name="groups" size="16px" class="q-mr-xs" />
                {{ props.row.ministry.name }}
              </q-chip>
            </div>
            <div v-else class="text-grey-6">—</div>
          </q-td>
        </template>

        <template v-slot:body-cell-contribution_date="props">
          <q-td :props="props">
            {{ formatDate(props.row.contribution_date) }}
          </q-td>
        </template>

        <template v-slot:body-cell-actions="props">
          <q-td :props="props">
            <q-btn-dropdown
              v-if="canManageContribution(props.row)"
              flat
              round
              dense
              icon="more_vert"
              dropdown-icon="none"
            >
              <q-list>
                <q-item clickable v-close-popup @click="viewContribution(props.row)">
                  <q-item-section avatar>
                    <q-icon name="visibility" />
                  </q-item-section>
                  <q-item-section>View Details</q-item-section>
                </q-item>
                <q-item clickable v-close-popup @click="editContribution(props.row)">
                  <q-item-section avatar>
                    <q-icon name="edit" />
                  </q-item-section>
                  <q-item-section>Edit</q-item-section>
                </q-item>
                <q-separator />
                <q-item
                  clickable
                  v-close-popup
                  @click="deleteContribution(props.row)"
                  class="text-negative"
                >
                  <q-item-section avatar>
                    <q-icon name="delete" color="negative" />
                  </q-item-section>
                  <q-item-section>Delete</q-item-section>
                </q-item>
              </q-list>
            </q-btn-dropdown>
          </q-td>
        </template>
      </q-table>
    </q-card>

    <!-- Create/Edit Dialog -->
    <q-dialog v-model="showCreateDialog" persistent>
      <q-card style="min-width: 600px; max-width: 800px">
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6">{{ editingContribution ? 'Edit' : 'Record' }} Contribution</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup @click="resetForm" />
        </q-card-section>

        <q-card-section>
          <q-form @submit="saveContribution" class="q-gutter-md">
            <q-select
              v-model="form.user_id"
              outlined
              label="Contributor *"
              :options="userOptions"
              option-value="id"
              option-label="name"
              emit-value
              map-options
              use-input
              input-debounce="300"
              @filter="filterUsers"
              :rules="[val => !!val || 'Contributor is required']"
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
                  v-model.number="form.amount"
                  outlined
                  type="number"
                  step="0.01"
                  label="Amount *"
                  :rules="[
                    val => !!val || 'Amount is required',
                    val => val > 0 || 'Amount must be greater than 0'
                  ]"
                >
                  <template v-slot:prepend>
                    <q-icon name="attach_money" />
                  </template>
                </q-input>
              </div>
              <div class="col-12 col-sm-6">
                <q-select
                  v-model="form.type"
                  outlined
                  label="Contribution Type *"
                  :options="typeOptions"
                  emit-value
                  map-options
                  :rules="[val => !!val || 'Type is required']"
                />
              </div>
            </div>

            <div class="row q-col-gutter-md">
              <div class="col-12 col-sm-6">
                <q-select
                  v-model="form.payment_method"
                  outlined
                  label="Payment Method *"
                  :options="paymentMethodOptions"
                  emit-value
                  map-options
                  :rules="[val => !!val || 'Payment method is required']"
                />
              </div>
              <div class="col-12 col-sm-6">
                <q-input
                  v-model="form.contribution_date"
                  outlined
                  label="Contribution Date *"
                  :rules="[val => !!val || 'Date is required']"
                >
                  <template v-slot:prepend>
                    <q-icon name="event" class="cursor-pointer">
                      <q-popup-proxy cover transition-show="scale" transition-hide="scale">
                        <q-date v-model="form.contribution_date" mask="YYYY-MM-DD">
                          <div class="row items-center justify-end">
                            <q-btn v-close-popup label="Close" color="primary" flat />
                          </div>
                        </q-date>
                      </q-popup-proxy>
                    </q-icon>
                  </template>
                </q-input>
              </div>
            </div>

            <q-select
              v-if="form.type === 'donation'"
              v-model="form.ministry_id"
              outlined
              label="Ministry (for donations)"
              :options="ministryOptions"
              option-value="id"
              option-label="name"
              emit-value
              map-options
              clearable
              use-input
              input-debounce="300"
              @filter="filterMinistries"
            >
              <template v-slot:prepend>
                <q-icon name="groups" />
              </template>
            </q-select>

            <q-input
              v-if="form.payment_method === 'bank_transfer' || form.payment_method === 'card'"
              v-model="form.reference_number"
              outlined
              label="Reference Number"
              hint="Transaction reference or receipt number"
            >
              <template v-slot:prepend>
                <q-icon name="confirmation_number" />
              </template>
            </q-input>

            <q-input
              v-model="form.notes"
              outlined
              type="textarea"
              label="Notes"
              rows="3"
              hint="Additional information about this contribution"
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

    <!-- View Details Dialog -->
    <q-dialog v-model="showDetailsDialog">
      <q-card style="min-width: 500px">
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6">Contribution Details</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>

        <q-card-section v-if="selectedContribution">
          <q-list>
            <q-item>
              <q-item-section>
                <q-item-label caption>Contributor</q-item-label>
                <q-item-label>{{ selectedContribution.user?.name || 'Unknown' }}</q-item-label>
              </q-item-section>
            </q-item>

            <q-item>
              <q-item-section>
                <q-item-label caption>Amount</q-item-label>
                <q-item-label class="text-h6 text-positive">
                  ${{ parseFloat(selectedContribution.amount).toFixed(2) }}
                </q-item-label>
              </q-item-section>
            </q-item>

            <q-item>
              <q-item-section>
                <q-item-label caption>Type</q-item-label>
                <q-item-label>
                  <q-chip
                    :color="getTypeColor(selectedContribution.type)"
                    text-color="white"
                    size="sm"
                  >
                    {{ capitalizeFirstLetter(selectedContribution.type) }}
                  </q-chip>
                </q-item-label>
              </q-item-section>
            </q-item>

            <q-item>
              <q-item-section>
                <q-item-label caption>Payment Method</q-item-label>
                <q-item-label>{{ formatPaymentMethod(selectedContribution.payment_method) }}</q-item-label>
              </q-item-section>
            </q-item>

            <q-item>
              <q-item-section>
                <q-item-label caption>Date</q-item-label>
                <q-item-label>{{ formatDate(selectedContribution.contribution_date) }}</q-item-label>
              </q-item-section>
            </q-item>

            <q-item v-if="selectedContribution.ministry">
              <q-item-section>
                <q-item-label caption>Ministry</q-item-label>
                <q-item-label>{{ selectedContribution.ministry.name }}</q-item-label>
              </q-item-section>
            </q-item>

            <q-item v-if="selectedContribution.reference_number">
              <q-item-section>
                <q-item-label caption>Reference Number</q-item-label>
                <q-item-label>{{ selectedContribution.reference_number }}</q-item-label>
              </q-item-section>
            </q-item>

            <q-item v-if="selectedContribution.notes">
              <q-item-section>
                <q-item-label caption>Notes</q-item-label>
                <q-item-label>{{ selectedContribution.notes }}</q-item-label>
              </q-item-section>
            </q-item>

            <q-item>
              <q-item-section>
                <q-item-label caption>Recorded On</q-item-label>
                <q-item-label>{{ formatDateTime(selectedContribution.created_at) }}</q-item-label>
              </q-item-section>
            </q-item>
          </q-list>
        </q-card-section>
      </q-card>
    </q-dialog>

    <!-- Reports Dialog -->
    <q-dialog v-model="showReportsDialog">
      <q-card style="min-width: 700px; max-width: 900px">
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6">Contribution Reports</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>

        <q-card-section>
          <q-tabs
            v-model="reportTab"
            dense
            class="text-grey"
            active-color="primary"
            indicator-color="primary"
            align="justify"
          >
            <q-tab name="summary" label="Summary" />
            <q-tab name="by-type" label="By Type" />
            <q-tab name="by-ministry" label="By Ministry" />
          </q-tabs>

          <q-separator />

          <q-tab-panels v-model="reportTab" animated>
            <q-tab-panel name="summary">
              <div v-if="loadingReports" class="text-center q-py-lg">
                <q-spinner color="primary" size="40px" />
              </div>
              <div v-else>
                <div class="text-h6 q-mb-md">Overall Summary</div>
                <q-list bordered separator>
                  <q-item>
                    <q-item-section>
                      <q-item-label>Total Contributions</q-item-label>
                    </q-item-section>
                    <q-item-section side>
                      <q-item-label class="text-h6 text-positive">
                        ${{ formatCurrency(reportData.summary?.total) }}
                      </q-item-label>
                    </q-item-section>
                  </q-item>
                  <q-item>
                    <q-item-section>
                      <q-item-label>Number of Contributors</q-item-label>
                    </q-item-section>
                    <q-item-section side>
                      <q-item-label class="text-h6">
                        {{ reportData.summary?.contributors_count || 0 }}
                      </q-item-label>
                    </q-item-section>
                  </q-item>
                  <q-item>
                    <q-item-section>
                      <q-item-label>Total Transactions</q-item-label>
                    </q-item-section>
                    <q-item-section side>
                      <q-item-label class="text-h6">
                        {{ reportData.summary?.total_count || 0 }}
                      </q-item-label>
                    </q-item-section>
                  </q-item>
                </q-list>
              </div>
            </q-tab-panel>

            <q-tab-panel name="by-type">
              <div v-if="loadingReports" class="text-center q-py-lg">
                <q-spinner color="primary" size="40px" />
              </div>
              <div v-else>
                <div class="text-h6 q-mb-md">Contributions by Type</div>
                <q-list bordered separator>
                  <q-item v-for="item in reportData.byType" :key="item.type">
                    <q-item-section avatar>
                      <q-chip
                        :color="getTypeColor(item.type)"
                        text-color="white"
                      >
                        {{ capitalizeFirstLetter(item.type) }}
                      </q-chip>
                    </q-item-section>
                    <q-item-section>
                      <q-item-label>{{ item.count || 0 }} contributions</q-item-label>
                    </q-item-section>
                    <q-item-section side>
                      <q-item-label class="text-h6 text-positive">
                        ${{ formatCurrency(item.total) }}
                      </q-item-label>
                    </q-item-section>
                  </q-item>
                </q-list>
              </div>
            </q-tab-panel>

            <q-tab-panel name="by-ministry">
              <div v-if="loadingReports" class="text-center q-py-lg">
                <q-spinner color="primary" size="40px" />
              </div>
              <div v-else-if="!reportData.byMinistry || reportData.byMinistry.length === 0">
                <div class="text-center q-py-lg text-grey-6">
                  No ministry-specific contributions yet
                </div>
              </div>
              <div v-else>
                <div class="text-h6 q-mb-md">Contributions by Ministry</div>
                <q-list bordered separator>
                  <q-item v-for="item in reportData.byMinistry" :key="item.ministry_id">
                    <q-item-section avatar>
                      <q-avatar color="purple" text-color="white">
                        <q-icon name="groups" />
                      </q-avatar>
                    </q-item-section>
                    <q-item-section>
                      <q-item-label>{{ item.ministry_name || 'Unknown Ministry' }}</q-item-label>
                      <q-item-label caption>{{ item.count || 0 }} contributions</q-item-label>
                    </q-item-section>
                    <q-item-section side>
                      <q-item-label class="text-h6 text-positive">
                        ${{ formatCurrency(item.total) }}
                      </q-item-label>
                    </q-item-section>
                  </q-item>
                </q-list>
              </div>
            </q-tab-panel>
          </q-tab-panels>
        </q-card-section>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useQuasar, date as qdate } from 'quasar'
import { api } from 'src/services/api'
import { useAuthStore } from 'src/stores/auth'

const $q = useQuasar()
const authStore = useAuthStore()

// Data
const contributions = ref([])
const loading = ref(false)
const saving = ref(false)
const loadingReports = ref(false)
const search = ref('')
const filters = ref({
  type: null,
  paymentMethod: null,
  startDate: null,
  endDate: null
})
const summary = ref({
  total: 0,
  tithe: 0,
  offering: 0,
  donation: 0
})
const tablePagination = ref({
  page: 1,
  rowsPerPage: 10
})

// Dialog & Forms
const showCreateDialog = ref(false)
const showDetailsDialog = ref(false)
const showReportsDialog = ref(false)
const editingContribution = ref(null)
const selectedContribution = ref(null)
const reportTab = ref('summary')
const reportData = ref({
  summary: {},
  byType: [],
  byMinistry: []
})
const form = ref({
  user_id: null,
  amount: null,
  type: 'tithe',
  payment_method: 'cash',
  contribution_date: qdate.formatDate(new Date(), 'YYYY-MM-DD'),
  ministry_id: null,
  reference_number: '',
  notes: ''
})

// Options
const userOptions = ref([])
const allUsers = ref([])
const ministryOptions = ref([])
const allMinistries = ref([])

const typeOptions = [
  { label: 'Tithe', value: 'tithe' },
  { label: 'Offering', value: 'offering' },
  { label: 'Donation', value: 'donation' }
]

const paymentMethodOptions = [
  { label: 'Cash', value: 'cash' },
  { label: 'Bank Transfer', value: 'bank_transfer' },
  { label: 'Card', value: 'card' }
]

const columns = [
  { name: 'user', label: 'Contributor', field: 'user', align: 'left' },
  { name: 'amount', label: 'Amount', field: 'amount', align: 'left', sortable: true },
  { name: 'type', label: 'Type', field: 'type', align: 'left', sortable: true },
  { name: 'payment_method', label: 'Payment Method', field: 'payment_method', align: 'left' },
  { name: 'ministry', label: 'Ministry', field: 'ministry', align: 'left' },
  { name: 'contribution_date', label: 'Date', field: 'contribution_date', align: 'left', sortable: true },
  { name: 'actions', label: 'Actions', field: 'actions', align: 'center' }
]

// Computed
const canRecordContribution = computed(() => {
  return authStore.hasAnyRole(['pastor', 'administrator', 'treasurer'])
})

// Methods
function canManageContribution(contribution) {
  if (!contribution) return false
  if (authStore.hasRole('administrator')) return true
  if (authStore.hasRole('pastor')) return true
  if (authStore.hasRole('treasurer')) return true
  return false
}

async function fetchContributions() {
  loading.value = true
  try {
    const params = {}

    if (search.value) {
      params.search = search.value
    }
    if (filters.value.type) {
      params.type = filters.value.type
    }
    if (filters.value.paymentMethod) {
      params.payment_method = filters.value.paymentMethod
    }
    if (filters.value.startDate) {
      params.start_date = filters.value.startDate
    }
    if (filters.value.endDate) {
      params.end_date = filters.value.endDate
    }

    const response = await api.get('/contributions', { params })
    contributions.value = Array.isArray(response.data) ? response.data : response.data.data || []
    
    // Fetch summary
    await fetchSummary()
  } catch (error) {
    contributions.value = []
    $q.notify({
      type: 'negative',
      message: 'Failed to load contributions',
      caption: error.response?.data?.message || error.message
    })
  } finally {
    loading.value = false
  }
}

async function fetchSummary() {
  try {
    const response = await api.get('/contributions/reports/summary')
    const data = response.data.data || response.data
    
    summary.value = {
      total: parseFloat(data.total || 0),
      tithe: parseFloat(data.tithe || 0),
      offering: parseFloat(data.offering || 0),
      donation: parseFloat(data.donation || 0)
    }
  } catch (error) {
    console.error('Failed to fetch summary:', error)
  }
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
    allMinistries.value = Array.isArray(response.data) ? response.data : response.data.data || []
    ministryOptions.value = allMinistries.value
  } catch (error) {
    console.error('Failed to fetch ministries:', error)
  }
}

let searchTimeout = null
function debouncedSearch() {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    fetchContributions()
  }, 500)
}

function clearSearch() {
  search.value = ''
  fetchContributions()
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

function filterMinistries(val, update) {
  update(() => {
    if (val === '') {
      ministryOptions.value = allMinistries.value
    } else {
      const needle = val.toLowerCase()
      ministryOptions.value = allMinistries.value.filter(
        ministry => ministry.name.toLowerCase().indexOf(needle) > -1
      )
    }
  })
}

function viewContribution(contribution) {
  selectedContribution.value = contribution
  showDetailsDialog.value = true
}

function editContribution(contribution) {
  editingContribution.value = contribution
  form.value = {
    user_id: contribution.user_id,
    amount: parseFloat(contribution.amount),
    type: contribution.type,
    payment_method: contribution.payment_method,
    contribution_date: contribution.contribution_date,
    ministry_id: contribution.ministry_id || null,
    reference_number: contribution.reference_number || '',
    notes: contribution.notes || ''
  }
  showCreateDialog.value = true
}

async function saveContribution() {
  saving.value = true
  try {
    if (editingContribution.value) {
      await api.put(`/contributions/${editingContribution.value.id}`, form.value)
      $q.notify({
        type: 'positive',
        message: 'Contribution updated successfully'
      })
    } else {
      await api.post('/contributions', form.value)
      $q.notify({
        type: 'positive',
        message: 'Contribution recorded successfully'
      })
    }
    
    showCreateDialog.value = false
    resetForm()
    fetchContributions()
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: 'Failed to save contribution',
      caption: error.response?.data?.message || error.message
    })
  } finally {
    saving.value = false
  }
}

function deleteContribution(contribution) {
  $q.dialog({
    title: 'Confirm Delete',
    message: `Are you sure you want to delete this contribution of $${parseFloat(contribution.amount).toFixed(2)}?`,
    cancel: true,
    persistent: true
  }).onOk(async () => {
    try {
      await api.delete(`/contributions/${contribution.id}`)
      $q.notify({
        type: 'positive',
        message: 'Contribution deleted successfully'
      })
      fetchContributions()
    } catch (error) {
      $q.notify({
        type: 'negative',
        message: 'Failed to delete contribution',
        caption: error.response?.data?.message || error.message
      })
    }
  })
}

async function fetchReports() {
  loadingReports.value = true
  try {
    const [summaryRes, byTypeRes, byMinistryRes] = await Promise.all([
      api.get('/contributions/reports/summary'),
      api.get('/contributions/reports/by-type'),
      api.get('/contributions/reports/by-ministry')
    ])

    reportData.value = {
      summary: summaryRes.data.data || summaryRes.data,
      byType: byTypeRes.data.data || byTypeRes.data || [],
      byMinistry: byMinistryRes.data.data || byMinistryRes.data || []
    }
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: 'Failed to load reports',
      caption: error.response?.data?.message || error.message
    })
  } finally {
    loadingReports.value = false
  }
}

// Watch for reports dialog opening
watch(showReportsDialog, (newVal) => {
  if (newVal) {
    fetchReports()
  }
})

function resetForm() {
  editingContribution.value = null
  form.value = {
    user_id: null,
    amount: null,
    type: 'tithe',
    payment_method: 'cash',
    contribution_date: qdate.formatDate(new Date(), 'YYYY-MM-DD'),
    ministry_id: null,
    reference_number: '',
    notes: ''
  }
}

function getTypeColor(type) {
  const colors = {
    tithe: 'green',
    offering: 'orange',
    donation: 'purple'
  }
  return colors[type] || 'blue'
}

function getTypeIcon(type) {
  const icons = {
    tithe: 'volunteer_activism',
    offering: 'card_giftcard',
    donation: 'favorite'
  }
  return icons[type] || 'payments'
}

function getPaymentMethodColor(method) {
  const colors = {
    cash: 'green',
    bank_transfer: 'blue',
    card: 'purple'
  }
  return colors[method] || 'grey'
}

function formatPaymentMethod(method) {
  if (!method) return 'N/A'
  return method.split('_').map(word => 
    word.charAt(0).toUpperCase() + word.slice(1)
  ).join(' ')
}

function capitalizeFirstLetter(string) {
  if (!string) return ''
  return string.charAt(0).toUpperCase() + string.slice(1)
}

function formatDate(dateString) {
  if (!dateString) return 'N/A'
  return qdate.formatDate(dateString, 'MMM DD, YYYY')
}

function formatDateTime(dateString) {
  if (!dateString) return 'N/A'
  return qdate.formatDate(dateString, 'MMM DD, YYYY h:mm A')
}

function formatCurrency(value) {
  const num = parseFloat(value || 0)
  return num.toFixed(2)
}

// Lifecycle
onMounted(() => {
  fetchContributions()
  fetchUsers()
  fetchMinistries()
})
</script>

<style scoped>
.summary-card {
  transition: all 0.3s;
}

.summary-card:hover {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  transform: translateY(-2px);
}
</style>
