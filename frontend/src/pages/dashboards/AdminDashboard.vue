<template>
  <q-page class="q-pa-md">
    <!-- Welcome Header -->
    <div class="q-mb-lg">
      <div class="text-h4 text-weight-bold">
        Administrator Dashboard 👑
      </div>
      <div class="text-subtitle1 text-grey-7">
        Welcome back, {{ authStore.user?.name }}
      </div>
    </div>

    <div class="row q-col-gutter-md">
      <!-- System Stats -->
      <div class="col-12 col-md-3">
        <q-card class="bg-primary text-white">
          <q-card-section>
            <div class="text-h6">Total Users</div>
            <div class="text-h3">{{ stats.totalUsers }}</div>
            <div class="text-caption">Registered Members</div>
          </q-card-section>
        </q-card>
      </div>

      <div class="col-12 col-md-3">
        <q-card class="bg-secondary text-white">
          <q-card-section>
            <div class="text-h6">Active Users</div>
            <div class="text-h3">{{ stats.activeUsers }}</div>
            <div class="text-caption">Last 30 Days</div>
          </q-card-section>
        </q-card>
      </div>

      <div class="col-12 col-md-3">
        <q-card class="bg-accent text-white">
          <q-card-section>
            <div class="text-h6">Total Events</div>
            <div class="text-h3">{{ stats.totalEvents }}</div>
            <div class="text-caption">All Time</div>
          </q-card-section>
        </q-card>
      </div>

      <div class="col-12 col-md-3">
        <q-card class="bg-positive text-white">
          <q-card-section>
            <div class="text-h6">Ministries</div>
            <div class="text-h3">{{ stats.totalMinistries }}</div>
            <div class="text-caption">Active Groups</div>
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
                  icon="person_add"
                  label="Add User"
                  @click="showAddUserDialog = true"
                />
              </div>

              <div class="col-12 col-sm-6 col-md-3">
                <q-btn
                  unelevated
                  color="secondary"
                  class="full-width q-py-md"
                  icon="campaign"
                  label="Create Announcement"
                  @click="$router.push('/app/announcements/create')"
                />
              </div>

              <div class="col-12 col-sm-6 col-md-3">
                <q-btn
                  unelevated
                  color="accent"
                  class="full-width q-py-md"
                  icon="assessment"
                  label="View Reports"
                  @click="$router.push('/app/reports')"
                />
              </div>

              <div class="col-12 col-sm-6 col-md-3">
                <q-btn
                  unelevated
                  color="positive"
                  class="full-width q-py-md"
                  icon="settings"
                  label="System Settings"
                  @click="$router.push('/app/settings')"
                />
              </div>
            </div>
          </q-card-section>
        </q-card>
      </div>

      <!-- Recent Users -->
      <div class="col-12 col-md-6">
        <q-card>
          <q-card-section>
            <div class="row items-center q-mb-md">
              <div class="col">
                <div class="text-h6">
                  <q-icon name="people" class="q-mr-sm" />
                  Recent Members
                </div>
              </div>
              <div class="col-auto">
                <q-btn
                  flat
                  dense
                  color="primary"
                  label="View All"
                  @click="$router.push('/app/users')"
                />
              </div>
            </div>

            <q-list separator v-if="recentUsers.length > 0">
              <q-item v-for="user in recentUsers" :key="user.id" clickable>
                <q-item-section avatar>
                  <q-avatar color="primary" text-color="white">
                    {{ user.name.charAt(0) }}
                  </q-avatar>
                </q-item-section>

                <q-item-section>
                  <q-item-label class="text-weight-bold">
                    {{ user.name }}
                  </q-item-label>
                  <q-item-label caption>{{ user.email }}</q-item-label>
                </q-item-section>

                <q-item-section side>
                  <q-badge
                    :color="user.is_active ? 'positive' : 'grey'"
                  >
                    {{ user.is_active ? 'Active' : 'Inactive' }}
                  </q-badge>
                </q-item-section>
              </q-item>
            </q-list>

            <div v-else class="text-center text-grey-6 q-py-lg">
              <q-icon name="people" size="48px" class="q-mb-md" />
              <div>No users found</div>
            </div>
          </q-card-section>
        </q-card>
      </div>

      <!-- System Activity -->
      <div class="col-12 col-md-6">
        <q-card>
          <q-card-section>
            <div class="text-h6 q-mb-md">
              <q-icon name="timeline" class="q-mr-sm" />
              System Activity
            </div>

            <q-timeline color="primary">
              <q-timeline-entry
                v-for="activity in recentActivity"
                :key="activity.id"
                :title="activity.title"
                :subtitle="activity.time"
                :icon="activity.icon"
              >
                {{ activity.description }}
              </q-timeline-entry>
            </q-timeline>

            <div v-if="recentActivity.length === 0" class="text-center text-grey-6 q-py-lg">
              <q-icon name="timeline" size="48px" class="q-mb-md" />
              <div>No recent activity</div>
            </div>
          </q-card-section>
        </q-card>
      </div>

      <!-- User Roles Distribution -->
      <div class="col-12 col-md-6">
        <q-card>
          <q-card-section>
            <div class="text-h6 q-mb-md">
              <q-icon name="admin_panel_settings" class="q-mr-sm" />
              User Roles
            </div>

            <div class="q-pa-md">
              <div v-for="role in userRoles" :key="role.name" class="q-mb-md">
                <div class="row items-center q-mb-xs">
                  <div class="col">
                    <span class="text-weight-medium">{{ role.display_name }}</span>
                  </div>
                  <div class="col-auto">
                    <span class="text-weight-bold">{{ role.count }}</span>
                  </div>
                </div>
                <q-linear-progress
                  :value="role.percentage / 100"
                  :color="role.color"
                  size="12px"
                  rounded
                />
                <div class="text-caption text-grey-7">{{ role.percentage }}%</div>
              </div>
            </div>
          </q-card-section>
        </q-card>
      </div>

      <!-- Monthly Overview -->
      <div class="col-12 col-md-6">
        <q-card>
          <q-card-section>
            <div class="text-h6 q-mb-md">
              <q-icon name="bar_chart" class="q-mr-sm" />
              Monthly Overview
            </div>

            <div class="row q-col-gutter-md">
              <div class="col-6">
                <div class="q-pa-md bg-blue-1 rounded-borders text-center">
                  <div class="text-subtitle2 text-grey-7">New Members</div>
                  <div class="text-h5 text-blue">{{ monthlyStats.newMembers }}</div>
                </div>
              </div>

              <div class="col-6">
                <div class="q-pa-md bg-green-1 rounded-borders text-center">
                  <div class="text-subtitle2 text-grey-7">Events Held</div>
                  <div class="text-h5 text-green">{{ monthlyStats.eventsHeld }}</div>
                </div>
              </div>

              <div class="col-6">
                <div class="q-pa-md bg-orange-1 rounded-borders text-center">
                  <div class="text-subtitle2 text-grey-7">Announcements</div>
                  <div class="text-h5 text-orange">{{ monthlyStats.announcements }}</div>
                </div>
              </div>

              <div class="col-6">
                <div class="q-pa-md bg-purple-1 rounded-borders text-center">
                  <div class="text-subtitle2 text-grey-7">Messages Sent</div>
                  <div class="text-h5 text-purple">{{ monthlyStats.messagesSent }}</div>
                </div>
              </div>
            </div>
          </q-card-section>
        </q-card>
      </div>
    </div>

    <!-- Add User Dialog -->
    <q-dialog v-model="showAddUserDialog">
      <q-card style="min-width: 500px">
        <q-card-section>
          <div class="text-h6">Add New User</div>
        </q-card-section>

        <q-card-section>
          <q-form @submit="addUser" class="q-gutter-md">
            <q-input
              v-model="userForm.name"
              label="Full Name *"
              outlined
              :rules="[(val) => !!val || 'Name is required']"
            />

            <q-input
              v-model="userForm.email"
              type="email"
              label="Email *"
              outlined
              :rules="[(val) => !!val || 'Email is required']"
            />

            <q-input
              v-model="userForm.password"
              type="password"
              label="Password *"
              outlined
              :rules="[(val) => val.length >= 6 || 'Password must be at least 6 characters']"
            />

            <q-select
              v-model="userForm.role"
              :options="['user', 'pastor', 'finance_committee', 'administrator']"
              label="Role *"
              outlined
              emit-value
              map-options
              :rules="[(val) => !!val || 'Role is required']"
            />
          </q-form>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Cancel" v-close-popup />
          <q-btn
            unelevated
            color="primary"
            label="Add User"
            @click="addUser"
            :loading="adding"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useAuthStore } from 'src/stores/auth';
import { usersAPI, dashboardAPI } from 'src/services/api';
import { Notify } from 'quasar';

const authStore = useAuthStore();

const recentUsers = ref([]);
const showAddUserDialog = ref(false);
const adding = ref(false);

const stats = ref({
  totalUsers: 0,
  activeUsers: 0,
  totalEvents: 0,
  totalMinistries: 0,
});

const monthlyStats = ref({
  newMembers: 0,
  eventsHeld: 0,
  announcements: 0,
  messagesSent: 0,
});

const userForm = ref({
  name: '',
  email: '',
  password: '',
  role: 'user',
});

const recentActivity = ref([]);
const userRolesData = ref([]);

const userRoles = computed(() => {
  const total = stats.value.totalUsers || 1;
  const roleColors = {
    user: 'blue',
    pastor: 'purple',
    finance_committee: 'green',
    administrator: 'orange',
  };
  
  const roleNames = {
    user: 'Members',
    pastor: 'Pastors',
    finance_committee: 'Finance Committee',
    administrator: 'Administrators',
  };

  return userRolesData.value.map(role => {
    const percentage = total > 0 ? Math.round((role.count / total) * 100) : 0;
    return {
      name: role.name,
      display_name: roleNames[role.name] || role.name,
      count: role.count,
      percentage,
      color: roleColors[role.name] || 'grey',
    };
  });
});

const fetchDashboardData = async () => {
  try {
    const response = await dashboardAPI.getAdminStats();
    const data = response.data.data;
    
    // Set stats
    stats.value = {
      totalUsers: data.total_users,
      activeUsers: data.active_users,
      totalEvents: data.total_events,
      totalMinistries: data.total_ministries,
    };

    // Set monthly stats
    monthlyStats.value = {
      newMembers: data.monthly_overview.new_members,
      eventsHeld: data.monthly_overview.events_held,
      announcements: data.monthly_overview.announcements,
      messagesSent: data.monthly_overview.messages_sent,
    };

    // Set recent users
    recentUsers.value = data.recent_users;

    // Set user roles
    userRolesData.value = Object.entries(data.user_roles).map(([name, count]) => ({
      name,
      count,
    }));
  } catch (error) {
    console.error('Error fetching dashboard data:', error);
    Notify.create({
      type: 'negative',
      message: 'Failed to load dashboard data',
    });
  }
};

const addUser = async () => {
  adding.value = true;
  try {
    await usersAPI.create(userForm.value);
    Notify.create({
      type: 'positive',
      message: 'User added successfully!',
    });
    showAddUserDialog.value = false;
    userForm.value = { name: '', email: '', password: '', role: 'user' };
    fetchDashboardData();
  } catch (error) {
    console.error('Error adding user:', error);
    Notify.create({
      type: 'negative',
      message: 'Failed to add user',
    });
  } finally {
    adding.value = false;
  }
};

onMounted(() => {
  fetchDashboardData();
});
</script>

