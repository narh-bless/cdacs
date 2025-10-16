<template>
  <q-page class="q-pa-md">
    <!-- Header -->
    <div class="row items-center justify-between q-mb-lg">
      <div class="col">
        <div class="text-h4 text-weight-bold">
          <q-icon name="people" class="q-mr-sm" />
          User Management
        </div>
        <div class="text-subtitle1 text-grey-7">
          Manage church members and their roles
        </div>
      </div>
      <div class="col-auto" v-if="authStore.isAdministrator">
        <q-btn
          unelevated
          color="primary"
          icon="person_add"
          label="Add User"
          @click="showAddDialog = true"
        />
      </div>
    </div>

    <!-- Filters -->
    <q-card class="q-mb-md">
      <q-card-section>
        <div class="row q-col-gutter-md">
          <!-- Search -->
          <div class="col-12 col-md-4">
            <q-input
              v-model="searchQuery"
              outlined
              dense
              placeholder="Search by name or email..."
              clearable
            >
              <template v-slot:prepend>
                <q-icon name="search" />
              </template>
            </q-input>
          </div>

          <!-- Role Filter -->
          <div class="col-12 col-md-3">
            <q-select
              v-model="filterRole"
              outlined
              dense
              label="Filter by Role"
              :options="roleOptions"
              clearable
              emit-value
              map-options
            >
              <template v-slot:prepend>
                <q-icon name="admin_panel_settings" />
              </template>
            </q-select>
          </div>

          <!-- Status Filter -->
          <div class="col-12 col-md-3">
            <q-select
              v-model="filterStatus"
              outlined
              dense
              label="Filter by Status"
              :options="statusOptions"
              clearable
              emit-value
              map-options
            >
              <template v-slot:prepend>
                <q-icon name="toggle_on" />
              </template>
            </q-select>
          </div>

          <!-- Refresh Button -->
          <div class="col-12 col-md-2">
            <q-btn
              unelevated
              color="primary"
              icon="refresh"
              label="Refresh"
              class="full-width"
              @click="fetchUsers"
              :loading="loading"
            />
          </div>
        </div>
      </q-card-section>
    </q-card>

    <!-- Users Table -->
    <q-card>
      <q-card-section>
        <q-table
          :rows="filteredUsers"
          :columns="columns"
          row-key="id"
          :loading="loading"
          :pagination="pagination"
          @request="onRequest"
          binary-state-sort
          flat
        >
          <!-- User Column with Avatar -->
          <template v-slot:body-cell-name="props">
            <q-td :props="props">
              <div class="row items-center no-wrap">
                <q-avatar color="primary" text-color="white" size="40px" class="q-mr-md">
                  {{ props.row.name.charAt(0).toUpperCase() }}
                </q-avatar>
                <div>
                  <div class="text-weight-bold">{{ props.row.name }}</div>
                  <div class="text-caption text-grey-7">{{ props.row.email }}</div>
                </div>
              </div>
            </q-td>
          </template>

          <!-- Roles Column -->
          <template v-slot:body-cell-roles="props">
            <q-td :props="props">
              <q-badge
                v-for="role in props.row.roles"
                :key="role.id"
                :color="getRoleColor(role.name)"
                class="q-mr-xs"
              >
                {{ getRoleDisplay(role.name) }}
              </q-badge>
              <q-badge v-if="!props.row.roles || props.row.roles.length === 0" color="grey">
                No Role
              </q-badge>
            </q-td>
          </template>

          <!-- Status Column -->
          <template v-slot:body-cell-is_active="props">
            <q-td :props="props">
              <q-badge :color="props.row.is_active ? 'positive' : 'grey'">
                {{ props.row.is_active ? 'Active' : 'Inactive' }}
              </q-badge>
            </q-td>
          </template>

          <!-- Phone Column -->
          <template v-slot:body-cell-phone="props">
            <q-td :props="props">
              {{ props.row.phone || 'N/A' }}
            </q-td>
          </template>

          <!-- Joined Date Column -->
          <template v-slot:body-cell-created_at="props">
            <q-td :props="props">
              {{ formatDate(props.row.created_at) }}
            </q-td>
          </template>

          <!-- Actions Column -->
          <template v-slot:body-cell-actions="props">
            <q-td :props="props">
              <q-btn
                flat
                dense
                round
                icon="visibility"
                color="primary"
                @click="viewUser(props.row)"
              >
                <q-tooltip>View Details</q-tooltip>
              </q-btn>

              <q-btn
                v-if="authStore.isAdministrator"
                flat
                dense
                round
                icon="edit"
                color="secondary"
                @click="editUser(props.row)"
              >
                <q-tooltip>Edit User</q-tooltip>
              </q-btn>

              <q-btn
                v-if="authStore.isAdministrator"
                flat
                dense
                round
                icon="admin_panel_settings"
                color="purple"
                @click="manageRoles(props.row)"
              >
                <q-tooltip>Manage Roles</q-tooltip>
              </q-btn>
            </q-td>
          </template>
        </q-table>
      </q-card-section>
    </q-card>

    <!-- Add User Dialog -->
    <q-dialog v-model="showAddDialog" persistent>
      <q-card style="min-width: 600px">
        <q-card-section class="row items-center">
          <div class="text-h6">
            <q-icon name="person_add" class="q-mr-sm" />
            Add New User
          </div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>

        <q-separator />

        <q-card-section class="q-pt-md">
          <q-form @submit.prevent="saveNewUser" class="q-gutter-md">
            <div class="row q-col-gutter-md">
              <div class="col-12">
                <q-input
                  v-model="userForm.name"
                  label="Full Name *"
                  outlined
                  :rules="[(val) => !!val || 'Name is required']"
                />
              </div>

              <div class="col-12 col-md-6">
                <q-input
                  v-model="userForm.email"
                  type="email"
                  label="Email *"
                  outlined
                  :rules="[
                    (val) => !!val || 'Email is required',
                    (val) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val) || 'Invalid email format'
                  ]"
                />
              </div>

              <div class="col-12 col-md-6">
                <q-input
                  v-model="userForm.phone"
                  label="Phone"
                  outlined
                />
              </div>

              <div class="col-12 col-md-6">
                <q-input
                  v-model="userForm.password"
                  type="password"
                  label="Password *"
                  outlined
                  :rules="[(val) => val.length >= 6 || 'Password must be at least 6 characters']"
                />
              </div>

              <div class="col-12 col-md-6">
                <q-input
                  v-model="userForm.password_confirmation"
                  type="password"
                  label="Confirm Password *"
                  outlined
                  :rules="[
                    (val) => val === userForm.password || 'Passwords do not match'
                  ]"
                />
              </div>

              <div class="col-12 col-md-6">
                <q-input
                  v-model="userForm.date_of_birth"
                  label="Date of Birth"
                  outlined
                  type="date"
                />
              </div>

              <div class="col-12 col-md-6">
                <q-select
                  v-model="userForm.gender"
                  :options="genderOptions"
                  label="Gender"
                  outlined
                  emit-value
                  map-options
                />
              </div>

              <div class="col-12">
                <q-input
                  v-model="userForm.address"
                  label="Address"
                  outlined
                />
              </div>

              <div class="col-12 col-md-4">
                <q-input
                  v-model="userForm.city"
                  label="City"
                  outlined
                />
              </div>

              <div class="col-12 col-md-4">
                <q-input
                  v-model="userForm.state"
                  label="State"
                  outlined
                />
              </div>

              <div class="col-12 col-md-4">
                <q-input
                  v-model="userForm.zip_code"
                  label="Zip Code"
                  outlined
                />
              </div>

              <div class="col-12 col-md-6">
                <q-select
                  v-model="userForm.marital_status"
                  :options="maritalStatusOptions"
                  label="Marital Status"
                  outlined
                  emit-value
                  map-options
                />
              </div>

              <div class="col-12 col-md-6">
                <q-input
                  v-model="userForm.occupation"
                  label="Occupation"
                  outlined
                />
              </div>

              <div class="col-12">
                <q-input
                  v-model="userForm.emergency_contact"
                  label="Emergency Contact"
                  outlined
                  hint="Name - Phone"
                />
              </div>
            </div>
          </q-form>
        </q-card-section>

        <q-separator />

        <q-card-actions align="right" class="q-pa-md">
          <q-btn flat label="Cancel" v-close-popup />
          <q-btn
            unelevated
            color="primary"
            label="Add User"
            icon="person_add"
            @click="saveNewUser"
            :loading="saving"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- Edit User Dialog -->
    <q-dialog v-model="showEditDialog" persistent>
      <q-card style="min-width: 600px">
        <q-card-section class="row items-center">
          <div class="text-h6">
            <q-icon name="edit" class="q-mr-sm" />
            Edit User
          </div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>

        <q-separator />

        <q-card-section class="q-pt-md">
          <q-form @submit.prevent="updateUser" class="q-gutter-md">
            <div class="row q-col-gutter-md">
              <div class="col-12">
                <q-input
                  v-model="editForm.name"
                  label="Full Name *"
                  outlined
                  :rules="[(val) => !!val || 'Name is required']"
                />
              </div>

              <div class="col-12 col-md-6">
                <q-input
                  v-model="editForm.email"
                  type="email"
                  label="Email *"
                  outlined
                  :rules="[(val) => !!val || 'Email is required']"
                />
              </div>

              <div class="col-12 col-md-6">
                <q-input
                  v-model="editForm.phone"
                  label="Phone"
                  outlined
                />
              </div>

              <div class="col-12 col-md-6">
                <q-input
                  v-model="editForm.date_of_birth"
                  label="Date of Birth"
                  outlined
                  type="date"
                />
              </div>

              <div class="col-12 col-md-6">
                <q-select
                  v-model="editForm.gender"
                  :options="genderOptions"
                  label="Gender"
                  outlined
                  emit-value
                  map-options
                />
              </div>

              <div class="col-12">
                <q-input
                  v-model="editForm.address"
                  label="Address"
                  outlined
                />
              </div>

              <div class="col-12 col-md-4">
                <q-input
                  v-model="editForm.city"
                  label="City"
                  outlined
                />
              </div>

              <div class="col-12 col-md-4">
                <q-input
                  v-model="editForm.state"
                  label="State"
                  outlined
                />
              </div>

              <div class="col-12 col-md-4">
                <q-input
                  v-model="editForm.zip_code"
                  label="Zip Code"
                  outlined
                />
              </div>

              <div class="col-12 col-md-6">
                <q-select
                  v-model="editForm.marital_status"
                  :options="maritalStatusOptions"
                  label="Marital Status"
                  outlined
                  emit-value
                  map-options
                />
              </div>

              <div class="col-12 col-md-6">
                <q-input
                  v-model="editForm.occupation"
                  label="Occupation"
                  outlined
                />
              </div>

              <div class="col-12">
                <q-input
                  v-model="editForm.emergency_contact"
                  label="Emergency Contact"
                  outlined
                  hint="Name - Phone"
                />
              </div>

              <div class="col-12" v-if="authStore.isAdministrator">
                <q-toggle
                  v-model="editForm.is_active"
                  label="Active User"
                  color="positive"
                />
              </div>
            </div>
          </q-form>
        </q-card-section>

        <q-separator />

        <q-card-actions align="right" class="q-pa-md">
          <q-btn flat label="Cancel" v-close-popup />
          <q-btn
            unelevated
            color="primary"
            label="Update"
            icon="save"
            @click="updateUser"
            :loading="saving"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- View User Details Dialog -->
    <q-dialog v-model="showViewDialog">
      <q-card style="min-width: 700px">
        <q-card-section class="row items-center bg-primary text-white">
          <div class="text-h6">
            <q-icon name="person" class="q-mr-sm" />
            User Details
          </div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup color="white" />
        </q-card-section>

        <q-separator />

        <q-card-section v-if="selectedUser">
          <q-tabs
            v-model="detailTab"
            dense
            class="text-grey"
            active-color="primary"
            indicator-color="primary"
            align="justify"
          >
            <q-tab name="info" label="Information" icon="info" />
            <q-tab name="contributions" label="Contributions" icon="payment" />
            <q-tab name="ministries" label="Ministries" icon="groups" />
          </q-tabs>

          <q-separator />

          <q-tab-panels v-model="detailTab" animated>
            <!-- Information Tab -->
            <q-tab-panel name="info">
              <div class="q-gutter-md">
                <div class="row">
                  <div class="col-12 text-center q-mb-md">
                    <q-avatar size="100px" color="primary" text-color="white">
                      {{ selectedUser.name.charAt(0).toUpperCase() }}
                    </q-avatar>
                    <div class="text-h5 q-mt-md">{{ selectedUser.name }}</div>
                    <div class="text-subtitle2 text-grey-7">{{ selectedUser.email }}</div>
                    <q-badge
                      :color="selectedUser.is_active ? 'positive' : 'grey'"
                      class="q-mt-sm"
                    >
                      {{ selectedUser.is_active ? 'Active' : 'Inactive' }}
                    </q-badge>
                  </div>
                </div>

                <q-separator />

                <div class="row q-col-gutter-md">
                  <div class="col-6">
                    <div class="text-caption text-grey-7">Phone</div>
                    <div class="text-body1">{{ selectedUser.phone || 'N/A' }}</div>
                  </div>

                  <div class="col-6">
                    <div class="text-caption text-grey-7">Date of Birth</div>
                    <div class="text-body1">{{ selectedUser.date_of_birth || 'N/A' }}</div>
                  </div>

                  <div class="col-6">
                    <div class="text-caption text-grey-7">Gender</div>
                    <div class="text-body1 text-capitalize">{{ selectedUser.gender || 'N/A' }}</div>
                  </div>

                  <div class="col-6">
                    <div class="text-caption text-grey-7">Marital Status</div>
                    <div class="text-body1 text-capitalize">{{ selectedUser.marital_status || 'N/A' }}</div>
                  </div>

                  <div class="col-12">
                    <div class="text-caption text-grey-7">Address</div>
                    <div class="text-body1">
                      {{ selectedUser.address || 'N/A' }}
                      <span v-if="selectedUser.city || selectedUser.state">
                        <br>{{ selectedUser.city }}{{ selectedUser.city && selectedUser.state ? ', ' : '' }}{{ selectedUser.state }} {{ selectedUser.zip_code }}
                      </span>
                    </div>
                  </div>

                  <div class="col-6">
                    <div class="text-caption text-grey-7">Occupation</div>
                    <div class="text-body1">{{ selectedUser.occupation || 'N/A' }}</div>
                  </div>

                  <div class="col-6">
                    <div class="text-caption text-grey-7">Emergency Contact</div>
                    <div class="text-body1">{{ selectedUser.emergency_contact || 'N/A' }}</div>
                  </div>

                  <div class="col-12">
                    <div class="text-caption text-grey-7">Roles</div>
                    <div class="q-mt-sm">
                      <q-badge
                        v-for="role in selectedUser.roles"
                        :key="role.id"
                        :color="getRoleColor(role.name)"
                        class="q-mr-xs"
                      >
                        {{ getRoleDisplay(role.name) }}
                      </q-badge>
                      <q-badge v-if="!selectedUser.roles || selectedUser.roles.length === 0" color="grey">
                        No Role Assigned
                      </q-badge>
                    </div>
                  </div>

                  <div class="col-6">
                    <div class="text-caption text-grey-7">Member Since</div>
                    <div class="text-body1">{{ formatDate(selectedUser.created_at) }}</div>
                  </div>
                </div>
              </div>
            </q-tab-panel>

            <!-- Contributions Tab -->
            <q-tab-panel name="contributions">
              <div v-if="loadingContributions" class="text-center q-py-lg">
                <q-spinner color="primary" size="50px" />
              </div>

              <div v-else-if="userContributions.length > 0">
                <q-list separator>
                  <q-item v-for="contribution in userContributions" :key="contribution.id">
                    <q-item-section>
                      <q-item-label class="text-weight-bold">
                        ${{ parseFloat(contribution.amount).toFixed(2) }}
                      </q-item-label>
                      <q-item-label caption>
                        {{ contribution.type }} - {{ contribution.payment_method }}
                      </q-item-label>
                      <q-item-label caption v-if="contribution.notes">
                        {{ contribution.notes }}
                      </q-item-label>
                    </q-item-section>

                    <q-item-section side>
                      <q-item-label>{{ formatDate(contribution.contribution_date) }}</q-item-label>
                      <q-badge :color="getContributionColor(contribution.type)">
                        {{ contribution.type }}
                      </q-badge>
                    </q-item-section>
                  </q-item>
                </q-list>

                <div class="q-mt-md q-pa-md bg-blue-1 rounded-borders">
                  <div class="text-subtitle2 text-grey-8">Total Contributions</div>
                  <div class="text-h5 text-primary">
                    ${{ calculateTotalContributions().toFixed(2) }}
                  </div>
                </div>
              </div>

              <div v-else class="text-center text-grey-6 q-py-lg">
                <q-icon name="payment" size="48px" class="q-mb-md" />
                <div>No contributions found</div>
              </div>
            </q-tab-panel>

            <!-- Ministries Tab -->
            <q-tab-panel name="ministries">
              <div v-if="loadingMinistries" class="text-center q-py-lg">
                <q-spinner color="primary" size="50px" />
              </div>

              <div v-else-if="userMinistries.length > 0">
                <q-list separator>
                  <q-item v-for="ministry in userMinistries" :key="ministry.id">
                    <q-item-section avatar>
                      <q-avatar color="secondary" text-color="white" icon="groups" />
                    </q-item-section>

                    <q-item-section>
                      <q-item-label class="text-weight-bold">
                        {{ ministry.name }}
                      </q-item-label>
                      <q-item-label caption>
                        {{ ministry.description }}
                      </q-item-label>
                      <q-item-label caption v-if="ministry.pivot">
                        Role: {{ ministry.pivot.role || 'Member' }} | 
                        Joined: {{ formatDate(ministry.pivot.joined_date) }}
                      </q-item-label>
                    </q-item-section>

                    <q-item-section side>
                      <q-badge :color="ministry.is_active ? 'positive' : 'grey'">
                        {{ ministry.is_active ? 'Active' : 'Inactive' }}
                      </q-badge>
                    </q-item-section>
                  </q-item>
                </q-list>
              </div>

              <div v-else class="text-center text-grey-6 q-py-lg">
                <q-icon name="groups" size="48px" class="q-mb-md" />
                <div>Not a member of any ministry</div>
              </div>
            </q-tab-panel>
          </q-tab-panels>
        </q-card-section>
      </q-card>
    </q-dialog>

    <!-- Manage Roles Dialog -->
    <q-dialog v-model="showRolesDialog" persistent>
      <q-card style="min-width: 400px">
        <q-card-section class="row items-center">
          <div class="text-h6">
            <q-icon name="admin_panel_settings" class="q-mr-sm" />
            Manage User Roles
          </div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>

        <q-separator />

        <q-card-section v-if="selectedUser">
          <div class="text-subtitle2 q-mb-md">
            User: <strong>{{ selectedUser.name }}</strong>
          </div>

          <div class="text-caption text-grey-7 q-mb-md">
            Current Roles:
          </div>
          <div class="q-mb-md">
            <q-badge
              v-for="role in selectedUser.roles"
              :key="role.id"
              :color="getRoleColor(role.name)"
              class="q-mr-xs q-mb-xs"
            >
              {{ getRoleDisplay(role.name) }}
            </q-badge>
            <q-badge v-if="!selectedUser.roles || selectedUser.roles.length === 0" color="grey">
              No Role Assigned
            </q-badge>
          </div>

          <q-separator class="q-mb-md" />

          <div class="text-caption text-grey-7 q-mb-md">
            Assign New Role:
          </div>

          <q-select
            v-model="selectedRole"
            :options="availableRoles"
            label="Select Role"
            outlined
            emit-value
            map-options
          />

          <div class="text-caption text-grey-6 q-mt-sm">
            Note: This is a simplified role assignment. In production, you would need role IDs from the backend.
          </div>
        </q-card-section>

        <q-separator />

        <q-card-actions align="right" class="q-pa-md">
          <q-btn flat label="Cancel" v-close-popup />
          <q-btn
            unelevated
            color="primary"
            label="Assign Role"
            icon="add"
            @click="assignRole"
            :loading="saving"
            :disable="!selectedRole"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useAuthStore } from 'src/stores/auth';
import { usersAPI } from 'src/services/api';
import { Notify } from 'quasar';

const authStore = useAuthStore();

// State
const users = ref([]);
const loading = ref(false);
const saving = ref(false);
const searchQuery = ref('');
const filterRole = ref(null);
const filterStatus = ref(null);
const showAddDialog = ref(false);
const showEditDialog = ref(false);
const showViewDialog = ref(false);
const showRolesDialog = ref(false);
const selectedUser = ref(null);
const detailTab = ref('info');
const userContributions = ref([]);
const userMinistries = ref([]);
const loadingContributions = ref(false);
const loadingMinistries = ref(false);
const selectedRole = ref(null);

// Pagination
const pagination = ref({
  sortBy: 'created_at',
  descending: true,
  page: 1,
  rowsPerPage: 10,
  rowsNumber: 0
});

// Form Data
const userForm = ref({
  name: '',
  email: '',
  phone: '',
  password: '',
  password_confirmation: '',
  date_of_birth: '',
  gender: '',
  address: '',
  city: '',
  state: '',
  zip_code: '',
  country: 'USA',
  marital_status: '',
  occupation: '',
  emergency_contact: ''
});

const editForm = ref({
  id: null,
  name: '',
  email: '',
  phone: '',
  date_of_birth: '',
  gender: '',
  address: '',
  city: '',
  state: '',
  zip_code: '',
  marital_status: '',
  occupation: '',
  emergency_contact: '',
  is_active: true
});

// Options
const roleOptions = [
  { label: 'Administrator', value: 'administrator' },
  { label: 'Pastor', value: 'pastor' },
  { label: 'Finance Committee', value: 'finance_committee' },
  { label: 'Member', value: 'user' }
];

const availableRoles = [
  { label: 'Administrator', value: 'administrator' },
  { label: 'Pastor', value: 'pastor' },
  { label: 'Finance Committee', value: 'finance_committee' },
  { label: 'Member', value: 'user' }
];

const statusOptions = [
  { label: 'Active', value: true },
  { label: 'Inactive', value: false }
];

const genderOptions = [
  { label: 'Male', value: 'male' },
  { label: 'Female', value: 'female' },
  { label: 'Other', value: 'other' }
];

const maritalStatusOptions = [
  { label: 'Single', value: 'single' },
  { label: 'Married', value: 'married' },
  { label: 'Divorced', value: 'divorced' },
  { label: 'Widowed', value: 'widowed' }
];

// Table Columns
const columns = [
  {
    name: 'name',
    required: true,
    label: 'User',
    align: 'left',
    field: 'name',
    sortable: true
  },
  {
    name: 'phone',
    label: 'Phone',
    align: 'left',
    field: 'phone',
    sortable: true
  },
  {
    name: 'roles',
    label: 'Roles',
    align: 'left',
    field: 'roles',
    sortable: false
  },
  {
    name: 'is_active',
    label: 'Status',
    align: 'center',
    field: 'is_active',
    sortable: true
  },
  {
    name: 'created_at',
    label: 'Joined',
    align: 'left',
    field: 'created_at',
    sortable: true
  },
  {
    name: 'actions',
    label: 'Actions',
    align: 'center',
    field: 'actions',
    sortable: false
  }
];

// Computed
const filteredUsers = computed(() => {
  let result = users.value;

  // Search filter
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    result = result.filter(user =>
      user.name.toLowerCase().includes(query) ||
      user.email.toLowerCase().includes(query)
    );
  }

  // Role filter
  if (filterRole.value) {
    result = result.filter(user =>
      user.roles?.some(role => role.name === filterRole.value)
    );
  }

  // Status filter
  if (filterStatus.value !== null) {
    result = result.filter(user => user.is_active === filterStatus.value);
  }

  return result;
});

// Methods
const fetchUsers = async () => {
  loading.value = true;
  try {
    const response = await usersAPI.getAll();
    users.value = response.data.data || response.data;
    pagination.value.rowsNumber = users.value.length;
  } catch (error) {
    console.error('Error fetching users:', error);
    Notify.create({
      type: 'negative',
      message: 'Failed to load users',
      position: 'top'
    });
  } finally {
    loading.value = false;
  }
};

const saveNewUser = async () => {
  if (!userForm.value.name || !userForm.value.email || !userForm.value.password) {
    Notify.create({
      type: 'warning',
      message: 'Please fill in all required fields',
      position: 'top'
    });
    return;
  }

  if (userForm.value.password !== userForm.value.password_confirmation) {
    Notify.create({
      type: 'warning',
      message: 'Passwords do not match',
      position: 'top'
    });
    return;
  }

  saving.value = true;
  try {
    await usersAPI.create(userForm.value);
    Notify.create({
      type: 'positive',
      message: 'User created successfully!',
      position: 'top'
    });
    showAddDialog.value = false;
    resetUserForm();
    fetchUsers();
  } catch (error) {
    console.error('Error creating user:', error);
    const message = error.response?.data?.message || 'Failed to create user';
    Notify.create({
      type: 'negative',
      message: message,
      position: 'top'
    });
  } finally {
    saving.value = false;
  }
};

const editUser = (user) => {
  editForm.value = {
    id: user.id,
    name: user.name,
    email: user.email,
    phone: user.phone || '',
    date_of_birth: user.date_of_birth || '',
    gender: user.gender || '',
    address: user.address || '',
    city: user.city || '',
    state: user.state || '',
    zip_code: user.zip_code || '',
    marital_status: user.marital_status || '',
    occupation: user.occupation || '',
    emergency_contact: user.emergency_contact || '',
    is_active: user.is_active ?? true
  };
  showEditDialog.value = true;
};

const updateUser = async () => {
  if (!editForm.value.name || !editForm.value.email) {
    Notify.create({
      type: 'warning',
      message: 'Please fill in all required fields',
      position: 'top'
    });
    return;
  }

  saving.value = true;
  try {
    const { id, ...updateData } = editForm.value;
    await usersAPI.update(id, updateData);
    Notify.create({
      type: 'positive',
      message: 'User updated successfully!',
      position: 'top'
    });
    showEditDialog.value = false;
    fetchUsers();
  } catch (error) {
    console.error('Error updating user:', error);
    const message = error.response?.data?.message || 'Failed to update user';
    Notify.create({
      type: 'negative',
      message: message,
      position: 'top'
    });
  } finally {
    saving.value = false;
  }
};

const viewUser = async (user) => {
  selectedUser.value = user;
  showViewDialog.value = true;
  detailTab.value = 'info';
  
  // Load contributions and ministries
  await Promise.all([
    fetchUserContributions(user.id),
    fetchUserMinistries(user.id)
  ]);
};

const fetchUserContributions = async (userId) => {
  loadingContributions.value = true;
  try {
    const response = await usersAPI.getContributions(userId);
    userContributions.value = response.data.data || response.data;
  } catch (error) {
    console.error('Error fetching contributions:', error);
    userContributions.value = [];
  } finally {
    loadingContributions.value = false;
  }
};

const fetchUserMinistries = async (userId) => {
  loadingMinistries.value = true;
  try {
    const response = await usersAPI.getMinistries(userId);
    userMinistries.value = response.data.data || response.data;
  } catch (error) {
    console.error('Error fetching ministries:', error);
    userMinistries.value = [];
  } finally {
    loadingMinistries.value = false;
  }
};

const manageRoles = (user) => {
  selectedUser.value = user;
  selectedRole.value = null;
  showRolesDialog.value = true;
};

const assignRole = async () => {
  if (!selectedRole.value) {
    Notify.create({
      type: 'warning',
      message: 'Please select a role',
      position: 'top'
    });
    return;
  }

  saving.value = true;
  try {
    // Note: This needs to be implemented in the backend with proper role IDs
    // For now, we'll show a placeholder message
    Notify.create({
      type: 'info',
      message: 'Role assignment functionality requires backend implementation with role IDs',
      position: 'top'
    });
    
    showRolesDialog.value = false;
    // await usersAPI.assignRole(selectedUser.value.id, roleId);
    // fetchUsers();
  } catch (error) {
    console.error('Error assigning role:', error);
    Notify.create({
      type: 'negative',
      message: 'Failed to assign role',
      position: 'top'
    });
  } finally {
    saving.value = false;
  }
};

const calculateTotalContributions = () => {
  return userContributions.value.reduce((total, contribution) => {
    return total + parseFloat(contribution.amount);
  }, 0);
};

const resetUserForm = () => {
  userForm.value = {
    name: '',
    email: '',
    phone: '',
    password: '',
    password_confirmation: '',
    date_of_birth: '',
    gender: '',
    address: '',
    city: '',
    state: '',
    zip_code: '',
    country: 'USA',
    marital_status: '',
    occupation: '',
    emergency_contact: ''
  };
};

const onRequest = (props) => {
  const { page, rowsPerPage, sortBy, descending } = props.pagination;
  pagination.value.page = page;
  pagination.value.rowsPerPage = rowsPerPage;
  pagination.value.sortBy = sortBy;
  pagination.value.descending = descending;
};

const getRoleColor = (roleName) => {
  const colors = {
    administrator: 'orange',
    pastor: 'purple',
    finance_committee: 'green',
    user: 'blue'
  };
  return colors[roleName] || 'grey';
};

const getRoleDisplay = (roleName) => {
  const names = {
    administrator: 'Administrator',
    pastor: 'Pastor',
    finance_committee: 'Finance Committee',
    user: 'Member'
  };
  return names[roleName] || roleName;
};

const getContributionColor = (type) => {
  const colors = {
    tithe: 'primary',
    offering: 'secondary',
    donation: 'positive',
    pledge: 'info'
  };
  return colors[type] || 'grey';
};

const formatDate = (dateString) => {
  if (!dateString) return 'N/A';
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
};

// Lifecycle
onMounted(() => {
  fetchUsers();
});
</script>

<style scoped>
.rounded-borders {
  border-radius: 8px;
}
</style>

