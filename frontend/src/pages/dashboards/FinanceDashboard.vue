<template>
  <q-page class="q-pa-md">
    <!-- Welcome Header -->
    <div class="q-mb-lg">
      <div class="text-h4 text-weight-bold">
        Finance Committee Dashboard 💰
      </div>
      <div class="text-subtitle1 text-grey-7">
        Welcome back, {{ authStore.user?.name }}
      </div>
    </div>

    <div class="row q-col-gutter-md">
      <!-- Financial Stats -->
      <div class="col-12 col-md-3">
        <q-card class="bg-green text-white">
          <q-card-section>
            <div class="text-h6">Total Contributions</div>
            <div class="text-h3">${{ stats.totalContributions.toLocaleString() }}</div>
            <div class="text-caption">This Month</div>
          </q-card-section>
        </q-card>
      </div>

      <div class="col-12 col-md-3">
        <q-card class="bg-blue text-white">
          <q-card-section>
            <div class="text-h6">Tithes</div>
            <div class="text-h3">${{ stats.tithes.toLocaleString() }}</div>
            <div class="text-caption">This Month</div>
          </q-card-section>
        </q-card>
      </div>

      <div class="col-12 col-md-3">
        <q-card class="bg-orange text-white">
          <q-card-section>
            <div class="text-h6">Offerings</div>
            <div class="text-h3">${{ stats.offerings.toLocaleString() }}</div>
            <div class="text-caption">This Month</div>
          </q-card-section>
        </q-card>
      </div>

      <div class="col-12 col-md-3">
        <q-card class="bg-purple text-white">
          <q-card-section>
            <div class="text-h6">Donations</div>
            <div class="text-h3">${{ stats.donations.toLocaleString() }}</div>
            <div class="text-caption">This Month</div>
          </q-card-section>
        </q-card>
      </div>

      <!-- Quick Actions -->
      <div class="col-12">
        <q-card>
          <q-card-section>
            <div class="text-h6 q-mb-md">
              <q-icon name="bolt" class="q-mr-sm" />
              Quick Actions
            </div>

            <div class="row q-col-gutter-sm">
              <div class="col-12 col-sm-6 col-md-3">
                <q-btn
                  unelevated
                  color="primary"
                  class="full-width q-py-md"
                  icon="add"
                  label="Record Contribution"
                  @click="showRecordDialog = true"
                />
              </div>

              <div class="col-12 col-sm-6 col-md-3">
                <q-btn
                  unelevated
                  color="secondary"
                  class="full-width q-py-md"
                  icon="assessment"
                  label="View Reports"
                  @click="$router.push('/app/contributions')"
                />
              </div>

              <div class="col-12 col-sm-6 col-md-3">
                <q-btn
                  unelevated
                  color="accent"
                  class="full-width q-py-md"
                  icon="download"
                  label="Export Data"
                  @click="exportData"
                />
              </div>

              <div class="col-12 col-sm-6 col-md-3">
                <q-btn
                  unelevated
                  color="positive"
                  class="full-width q-py-md"
                  icon="receipt"
                  label="Generate Report"
                  @click="generateReport"
                />
              </div>
            </div>
          </q-card-section>
        </q-card>
      </div>

      <!-- Recent Contributions -->
      <div class="col-12 col-md-8">
        <q-card>
          <q-card-section>
            <div class="row items-center q-mb-md">
              <div class="col">
                <div class="text-h6">
                  <q-icon name="payments" class="q-mr-sm" />
                  Recent Contributions
                </div>
              </div>
              <div class="col-auto">
                <q-btn
                  flat
                  dense
                  color="primary"
                  label="View All"
                  @click="$router.push('/app/contributions')"
                />
              </div>
            </div>

            <q-table
              :rows="recentContributions"
              :columns="contributionColumns"
              row-key="id"
              flat
              :rows-per-page-options="[5]"
              v-if="recentContributions.length > 0"
            >
              <template v-slot:body-cell-amount="props">
                <q-td :props="props">
                  <span class="text-weight-bold text-green">
                    ${{ props.row.amount.toLocaleString() }}
                  </span>
                </q-td>
              </template>

              <template v-slot:body-cell-type="props">
                <q-td :props="props">
                  <q-chip
                    :color="getTypeColor(props.row.type)"
                    text-color="white"
                    size="sm"
                  >
                    {{ props.row.type }}
                  </q-chip>
                </q-td>
              </template>
            </q-table>

            <div v-else class="text-center text-grey-6 q-py-lg">
              <q-icon name="payments" size="48px" class="q-mb-md" />
              <div>No contributions recorded yet</div>
            </div>
          </q-card-section>
        </q-card>
      </div>

      <!-- Contributions by Type Chart -->
      <div class="col-12 col-md-4">
        <q-card>
          <q-card-section>
            <div class="text-h6 q-mb-md">
              <q-icon name="pie_chart" class="q-mr-sm" />
              By Type
            </div>

            <div class="q-pa-md">
              <div v-for="type in contributionTypes" :key="type.name" class="q-mb-md">
                <div class="row items-center q-mb-xs">
                  <div class="col">
                    <span class="text-weight-medium">{{ type.name }}</span>
                  </div>
                  <div class="col-auto">
                    <span class="text-weight-bold">${{ type.amount.toLocaleString() }}</span>
                  </div>
                </div>
                <q-linear-progress
                  :value="type.percentage / 100"
                  :color="type.color"
                  size="12px"
                  rounded
                />
                <div class="text-caption text-grey-7">{{ type.percentage }}%</div>
              </div>
            </div>
          </q-card-section>
        </q-card>
      </div>

      <!-- Monthly Summary -->
      <div class="col-12">
        <q-card>
          <q-card-section>
            <div class="text-h6 q-mb-md">
              <q-icon name="calendar_month" class="q-mr-sm" />
              Monthly Summary
            </div>

            <div class="row q-col-gutter-md">
              <div class="col-12 col-md-4">
                <div class="q-pa-md bg-green-1 rounded-borders">
                  <div class="text-subtitle2 text-grey-7">Total Income</div>
                  <div class="text-h5 text-green">${{ stats.totalContributions.toLocaleString() }}</div>
                </div>
              </div>

              <div class="col-12 col-md-4">
                <div class="q-pa-md bg-blue-1 rounded-borders">
                  <div class="text-subtitle2 text-grey-7">Total Contributors</div>
                  <div class="text-h5 text-blue">{{ stats.totalContributors }}</div>
                </div>
              </div>

              <div class="col-12 col-md-4">
                <div class="q-pa-md bg-orange-1 rounded-borders">
                  <div class="text-subtitle2 text-grey-7">Average Contribution</div>
                  <div class="text-h5 text-orange">${{ stats.averageContribution.toLocaleString() }}</div>
                </div>
              </div>
            </div>
          </q-card-section>
        </q-card>
      </div>
    </div>

    <!-- Record Contribution Dialog -->
    <q-dialog v-model="showRecordDialog">
      <q-card style="min-width: 500px">
        <q-card-section>
          <div class="text-h6">Record Contribution</div>
        </q-card-section>

        <q-card-section>
          <q-form @submit="recordContribution" class="q-gutter-md">
            <q-select
              v-model="contributionForm.type"
              :options="['tithe', 'offering', 'donation', 'special']"
              label="Type *"
              outlined
              emit-value
              map-options
              :rules="[(val) => !!val || 'Type is required']"
            />

            <q-input
              v-model.number="contributionForm.amount"
              type="number"
              label="Amount *"
              outlined
              prefix="$"
              :rules="[(val) => val > 0 || 'Amount must be greater than 0']"
            />

            <q-select
              v-model="contributionForm.payment_method"
              :options="['cash', 'check', 'card', 'bank_transfer', 'online']"
              label="Payment Method *"
              outlined
              emit-value
              map-options
              :rules="[(val) => !!val || 'Payment method is required']"
            />

            <q-input
              v-model="contributionForm.notes"
              label="Notes"
              type="textarea"
              outlined
              rows="3"
            />
          </q-form>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Cancel" v-close-popup />
          <q-btn
            unelevated
            color="primary"
            label="Record"
            @click="recordContribution"
            :loading="recording"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useAuthStore } from 'src/stores/auth';
import { contributionsAPI, dashboardAPI } from 'src/services/api';
import { date, Notify } from 'quasar';

const authStore = useAuthStore();

const recentContributions = ref([]);
const showRecordDialog = ref(false);
const recording = ref(false);

const stats = ref({
  totalContributions: 0,
  tithes: 0,
  offerings: 0,
  donations: 0,
  totalContributors: 0,
  averageContribution: 0,
});

const contributionForm = ref({
  type: '',
  amount: 0,
  payment_method: '',
  notes: '',
});

const contributionColumns = [
  {
    name: 'date',
    label: 'Date',
    field: 'contribution_date',
    format: (val) => date.formatDate(val, 'MMM DD, YYYY'),
    align: 'left',
  },
  {
    name: 'contributor',
    label: 'Contributor',
    field: (row) => row.user?.name || 'N/A',
    align: 'left',
  },
  {
    name: 'type',
    label: 'Type',
    field: 'type',
    align: 'left',
  },
  {
    name: 'amount',
    label: 'Amount',
    field: 'amount',
    align: 'right',
  },
  {
    name: 'method',
    label: 'Method',
    field: 'payment_method',
    align: 'left',
  },
];

const contributionTypes = computed(() => {
  const total = stats.value.totalContributions || 1;
  return [
    {
      name: 'Tithes',
      amount: stats.value.tithes,
      percentage: Math.round((stats.value.tithes / total) * 100),
      color: 'blue',
    },
    {
      name: 'Offerings',
      amount: stats.value.offerings,
      percentage: Math.round((stats.value.offerings / total) * 100),
      color: 'orange',
    },
    {
      name: 'Donations',
      amount: stats.value.donations,
      percentage: Math.round((stats.value.donations / total) * 100),
      color: 'purple',
    },
  ];
});

const fetchDashboardData = async () => {
  try {
    const response = await dashboardAPI.getFinanceStats();
    const data = response.data.data;

    // Set stats
    stats.value = {
      totalContributions: parseFloat(data.total_contributions) || 0,
      tithes: parseFloat(data.tithes) || 0,
      offerings: parseFloat(data.offerings) || 0,
      donations: parseFloat(data.donations) || 0,
      totalContributors: data.total_contributors || 0,
      averageContribution: parseFloat(data.average_contribution) || 0,
    };

    // Set recent contributions
    recentContributions.value = data.recent_contributions || [];
  } catch (error) {
    console.error('Error fetching dashboard data:', error);
    Notify.create({
      type: 'negative',
      message: 'Failed to load dashboard data',
    });
  }
};

const recordContribution = async () => {
  recording.value = true;
  try {
    await contributionsAPI.create(contributionForm.value);
    Notify.create({
      type: 'positive',
      message: 'Contribution recorded successfully!',
    });
    showRecordDialog.value = false;
    contributionForm.value = { type: '', amount: 0, payment_method: '', notes: '' };
    fetchDashboardData();
  } catch (error) {
    console.error('Error recording contribution:', error);
    Notify.create({
      type: 'negative',
      message: 'Failed to record contribution',
    });
  } finally {
    recording.value = false;
  }
};

const getTypeColor = (type) => {
  const colors = {
    tithe: 'blue',
    offering: 'orange',
    donation: 'purple',
    special: 'green',
  };
  return colors[type] || 'grey';
};

const exportData = () => {
  Notify.create({
    type: 'info',
    message: 'Export functionality coming soon!',
  });
};

const generateReport = () => {
  Notify.create({
    type: 'info',
    message: 'Report generation coming soon!',
  });
};

onMounted(() => {
  fetchDashboardData();
});
</script>

