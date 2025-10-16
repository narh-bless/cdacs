<template>
  <q-page class="q-pa-md">
    <!-- Welcome Header -->
    <div class="q-mb-lg">
      <div class="text-h4 text-weight-bold">
        Welcome, {{ authStore.user?.name }}! 🙏
      </div>
      <div class="text-subtitle1 text-grey-7">
        Member since {{ memberSince }}
      </div>
    </div>

    <div class="row q-col-gutter-md">
      <!-- Quick Stats -->
      <div class="col-12 col-md-3">
        <q-card class="bg-primary text-white">
          <q-card-section>
            <div class="text-h6">My Contributions</div>
            <div class="text-h3">{{ stats.contributions }}</div>
            <div class="text-caption">Total Records</div>
          </q-card-section>
        </q-card>
      </div>

      <div class="col-12 col-md-3">
        <q-card class="bg-secondary text-white">
          <q-card-section>
            <div class="text-h6">My Ministries</div>
            <div class="text-h3">{{ stats.ministries }}</div>
            <div class="text-caption">Active Memberships</div>
          </q-card-section>
        </q-card>
      </div>

      <div class="col-12 col-md-3">
        <q-card class="bg-accent text-white">
          <q-card-section>
            <div class="text-h6">Unread Messages</div>
            <div class="text-h3">{{ stats.unreadMessages }}</div>
            <div class="text-caption">New Messages</div>
          </q-card-section>
        </q-card>
      </div>

      <div class="col-12 col-md-3">
        <q-card class="bg-positive text-white">
          <q-card-section>
            <div class="text-h6">Upcoming Events</div>
            <div class="text-h3">{{ stats.upcomingEvents }}</div>
            <div class="text-caption">This Month</div>
          </q-card-section>
        </q-card>
      </div>

      <!-- Recent Announcements -->
      <div class="col-12 col-md-8">
        <q-card>
          <q-card-section>
            <div class="text-h6 q-mb-md">
              <q-icon name="campaign" class="q-mr-sm" />
              Recent Announcements
            </div>

            <q-list separator v-if="announcements.length > 0">
              <q-item
                v-for="announcement in announcements"
                :key="announcement.id"
                clickable
                @click="viewAnnouncement(announcement.id)"
              >
                <q-item-section avatar>
                  <q-avatar
                    :color="getPriorityColor(announcement.priority)"
                    text-color="white"
                    icon="campaign"
                  />
                </q-item-section>

                <q-item-section>
                  <q-item-label class="text-weight-bold">
                    {{ announcement.title }}
                  </q-item-label>
                  <q-item-label caption lines="2">
                    {{ announcement.content }}
                  </q-item-label>
                  <q-item-label caption class="text-grey-6">
                    {{ formatDate(announcement.published_at) }}
                  </q-item-label>
                </q-item-section>

                <q-item-section side>
                  <q-chip
                    :color="getPriorityColor(announcement.priority)"
                    text-color="white"
                    size="sm"
                  >
                    {{ announcement.priority }}
                  </q-chip>
                </q-item-section>
              </q-item>
            </q-list>

            <div v-else class="text-center text-grey-6 q-py-lg">
              <q-icon name="info" size="48px" class="q-mb-md" />
              <div>No announcements available</div>
            </div>
          </q-card-section>

          <q-card-actions align="right">
            <q-btn
              flat
              color="primary"
              label="View All"
              @click="$router.push('/app/announcements')"
            />
          </q-card-actions>
        </q-card>
      </div>

      <!-- Upcoming Events -->
      <div class="col-12 col-md-4">
        <q-card>
          <q-card-section>
            <div class="text-h6 q-mb-md">
              <q-icon name="event" class="q-mr-sm" />
              Upcoming Events
            </div>

            <q-list separator v-if="events.length > 0">
              <q-item
                v-for="event in events"
                :key="event.id"
                clickable
                @click="viewEvent(event.id)"
              >
                <q-item-section avatar>
                  <q-avatar color="orange" text-color="white">
                    {{ formatDay(event.start_date) }}
                  </q-avatar>
                </q-item-section>

                <q-item-section>
                  <q-item-label class="text-weight-bold">
                    {{ event.title }}
                  </q-item-label>
                  <q-item-label caption>
                    {{ formatDateTime(event.start_date) }}
                  </q-item-label>
                  <q-item-label caption>
                    <q-icon name="place" size="xs" />
                    {{ event.location }}
                  </q-item-label>
                </q-item-section>
              </q-item>
            </q-list>

            <div v-else class="text-center text-grey-6 q-py-lg">
              <q-icon name="event_busy" size="48px" class="q-mb-md" />
              <div>No upcoming events</div>
            </div>
          </q-card-section>

          <q-card-actions align="right">
            <q-btn
              flat
              color="primary"
              label="View All"
              @click="$router.push('/app/events')"
            />
          </q-card-actions>
        </q-card>
      </div>

      <!-- My Ministries -->
      <div class="col-12 col-md-6">
        <q-card>
          <q-card-section>
            <div class="text-h6 q-mb-md">
              <q-icon name="groups" class="q-mr-sm" />
              My Ministries
            </div>

            <q-list separator v-if="ministries.length > 0">
              <q-item
                v-for="ministry in ministries"
                :key="ministry.id"
                clickable
                @click="viewMinistry(ministry.id)"
              >
                <q-item-section avatar>
                  <q-avatar color="purple" text-color="white" icon="groups" />
                </q-item-section>

                <q-item-section>
                  <q-item-label class="text-weight-bold">
                    {{ ministry.name }}
                  </q-item-label>
                  <q-item-label caption>
                    {{ ministry.description }}
                  </q-item-label>
                </q-item-section>

                <q-item-section side>
                  <q-badge color="green" v-if="ministry.pivot?.is_active">
                    Active
                  </q-badge>
                </q-item-section>
              </q-item>
            </q-list>

            <div v-else class="text-center text-grey-6 q-py-lg">
              <q-icon name="group_off" size="48px" class="q-mb-md" />
              <div>Not a member of any ministry yet</div>
            </div>
          </q-card-section>

          <q-card-actions align="right">
            <q-btn
              flat
              color="primary"
              label="Browse Ministries"
              @click="$router.push('/app/ministries')"
            />
          </q-card-actions>
        </q-card>
      </div>

      <!-- Quick Actions -->
      <div class="col-12 col-md-6">
        <q-card>
          <q-card-section>
            <div class="text-h6 q-mb-md">
              <q-icon name="bolt" class="q-mr-sm" />
              Quick Actions
            </div>

            <div class="row q-col-gutter-sm">
              <div class="col-6">
                <q-btn
                  unelevated
                  color="primary"
                  class="full-width"
                  icon="message"
                  label="Send Message"
                  @click="$router.push('/app/messages/compose')"
                />
              </div>

              <div class="col-6">
                <q-btn
                  unelevated
                  color="secondary"
                  class="full-width"
                  icon="person"
                  label="My Profile"
                  @click="$router.push('/app/profile')"
                />
              </div>

              <div class="col-6">
                <q-btn
                  unelevated
                  color="accent"
                  class="full-width"
                  icon="attach_money"
                  label="My Contributions"
                  @click="viewMyContributions"
                />
              </div>

              <div class="col-6">
                <q-btn
                  unelevated
                  color="positive"
                  class="full-width"
                  icon="event"
                  label="View Events"
                  @click="$router.push('/app/events')"
                />
              </div>
            </div>
          </q-card-section>
        </q-card>
      </div>
    </div>
  </q-page>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from 'src/stores/auth';
import {
  dashboardAPI,
} from 'src/services/api';
import { date, Notify } from 'quasar';

const router = useRouter();
const authStore = useAuthStore();

const announcements = ref([]);
const events = ref([]);
const ministries = ref([]);
const stats = ref({
  contributions: 0,
  ministries: 0,
  unreadMessages: 0,
  upcomingEvents: 0,
});

const memberSince = computed(() => {
  if (authStore.user?.created_at) {
    return date.formatDate(authStore.user.created_at, 'MMMM YYYY');
  }
  return 'Recently';
});

const fetchDashboardData = async () => {
  try {
    const response = await dashboardAPI.getUserStats();
    const data = response.data.data;

    // Set stats
    stats.value = {
      contributions: data.total_contributions,
      ministries: data.total_ministries,
      unreadMessages: data.unread_messages,
      upcomingEvents: data.upcoming_events,
    };

    // Set lists
    announcements.value = data.recent_announcements || [];
    events.value = data.upcoming_events_list || [];
    ministries.value = data.ministries || [];
  } catch (error) {
    console.error('Error fetching dashboard data:', error);
    Notify.create({
      type: 'negative',
      message: 'Failed to load dashboard data',
    });
  }
};

const getPriorityColor = (priority) => {
  const colors = {
    high: 'red',
    medium: 'orange',
    low: 'blue',
  };
  return colors[priority] || 'grey';
};

const formatDate = (dateString) => {
  return date.formatDate(dateString, 'MMM DD, YYYY');
};

const formatDateTime = (dateString) => {
  return date.formatDate(dateString, 'MMM DD, YYYY h:mm A');
};

const formatDay = (dateString) => {
  return date.formatDate(dateString, 'DD');
};

const viewAnnouncement = (id) => {
  router.push(`/app/announcements/${id}`);
};

const viewEvent = (id) => {
  router.push(`/app/events/${id}`);
};

const viewMinistry = (id) => {
  router.push(`/app/ministries/${id}`);
};

const viewMyContributions = () => {
  router.push('/app/contributions');
};

onMounted(() => {
  fetchDashboardData();
});
</script>

