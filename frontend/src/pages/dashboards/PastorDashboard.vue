<template>
  <q-page class="q-pa-md">
    <!-- Welcome Header -->
    <div class="q-mb-lg">
      <div class="text-h4 text-weight-bold">
        Pastor Dashboard 🙏
      </div>
      <div class="text-subtitle1 text-grey-7">
        Welcome back, {{ authStore.user?.name }}
      </div>
    </div>

    <div class="row q-col-gutter-md">
      <!-- Quick Stats -->
      <div class="col-12 col-md-3">
        <q-card class="bg-primary text-white">
          <q-card-section>
            <div class="text-h6">Total Members</div>
            <div class="text-h3">{{ stats.totalMembers }}</div>
            <div class="text-caption">Active Church Members</div>
          </q-card-section>
        </q-card>
      </div>

      <div class="col-12 col-md-3">
        <q-card class="bg-secondary text-white">
          <q-card-section>
            <div class="text-h6">Announcements</div>
            <div class="text-h3">{{ stats.announcements }}</div>
            <div class="text-caption">Published This Month</div>
          </q-card-section>
        </q-card>
      </div>

      <div class="col-12 col-md-3">
        <q-card class="bg-accent text-white">
          <q-card-section>
            <div class="text-h6">Upcoming Events</div>
            <div class="text-h3">{{ stats.upcomingEvents }}</div>
            <div class="text-caption">Scheduled Events</div>
          </q-card-section>
        </q-card>
      </div>

      <div class="col-12 col-md-3">
        <q-card class="bg-positive text-white">
          <q-card-section>
            <div class="text-h6">Ministries</div>
            <div class="text-h3">{{ stats.ministries }}</div>
            <div class="text-caption">Active Ministries</div>
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
                  icon="campaign"
                  label="New Announcement"
                  @click="$router.push('/app/announcements/create')"
                />
              </div>

              <div class="col-12 col-sm-6 col-md-3">
                <q-btn
                  unelevated
                  color="secondary"
                  class="full-width q-py-md"
                  icon="event"
                  label="Create Event"
                  @click="$router.push('/app/events/create')"
                />
              </div>

              <div class="col-12 col-sm-6 col-md-3">
                <q-btn
                  unelevated
                  color="accent"
                  class="full-width q-py-md"
                  icon="send"
                  label="Broadcast Message"
                  @click="showBroadcastDialog = true"
                />
              </div>

              <div class="col-12 col-sm-6 col-md-3">
                <q-btn
                  unelevated
                  color="positive"
                  class="full-width q-py-md"
                  icon="groups"
                  label="Manage Ministries"
                  @click="$router.push('/app/ministries')"
                />
              </div>
            </div>
          </q-card-section>
        </q-card>
      </div>

      <!-- Recent Announcements -->
      <div class="col-12 col-md-6">
        <q-card>
          <q-card-section>
            <div class="row items-center q-mb-md">
              <div class="col">
                <div class="text-h6">
                  <q-icon name="campaign" class="q-mr-sm" />
                  My Announcements
                </div>
              </div>
              <div class="col-auto">
                <q-btn
                  flat
                  dense
                  color="primary"
                  icon="add"
                  label="New"
                  @click="$router.push('/app/announcements/create')"
                />
              </div>
            </div>

            <q-list separator v-if="announcements.length > 0">
              <q-item
                v-for="announcement in announcements"
                :key="announcement.id"
                clickable
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
                  <q-item-label caption>
                    {{ formatDate(announcement.created_at) }}
                  </q-item-label>
                </q-item-section>

                <q-item-section side>
                  <q-badge
                    :color="announcement.is_published ? 'positive' : 'grey'"
                  >
                    {{ announcement.is_published ? 'Published' : 'Draft' }}
                  </q-badge>
                </q-item-section>
              </q-item>
            </q-list>

            <div v-else class="text-center text-grey-6 q-py-lg">
              <q-icon name="campaign" size="48px" class="q-mb-md" />
              <div>No announcements yet</div>
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
      <div class="col-12 col-md-6">
        <q-card>
          <q-card-section>
            <div class="row items-center q-mb-md">
              <div class="col">
                <div class="text-h6">
                  <q-icon name="event" class="q-mr-sm" />
                  Upcoming Events
                </div>
              </div>
              <div class="col-auto">
                <q-btn
                  flat
                  dense
                  color="primary"
                  icon="add"
                  label="New"
                  @click="$router.push('/app/events/create')"
                />
              </div>
            </div>

            <q-list separator v-if="events.length > 0">
              <q-item v-for="event in events" :key="event.id" clickable>
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

                <q-item-section side>
                  <q-chip :color="event.type === 'service' ? 'primary' : 'grey'" text-color="white" size="sm">
                    {{ event.type }}
                  </q-chip>
                </q-item-section>
              </q-item>
            </q-list>

            <div v-else class="text-center text-grey-6 q-py-lg">
              <q-icon name="event" size="48px" class="q-mb-md" />
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

      <!-- Ministry Overview -->
      <div class="col-12">
        <q-card>
          <q-card-section>
            <div class="text-h6 q-mb-md">
              <q-icon name="groups" class="q-mr-sm" />
              Ministry Overview
            </div>

            <div class="row q-col-gutter-md">
              <div
                v-for="ministry in ministries"
                :key="ministry.id"
                class="col-12 col-sm-6 col-md-4"
              >
                <q-card flat bordered>
                  <q-card-section>
                    <div class="text-subtitle1 text-weight-bold">
                      {{ ministry.name }}
                    </div>
                    <div class="text-caption text-grey-7">
                      {{ ministry.description }}
                    </div>
                    <div class="q-mt-sm">
                      <q-chip size="sm" color="primary" text-color="white">
                        {{ ministry.members_count || 0 }} Members
                      </q-chip>
                    </div>
                  </q-card-section>
                  <q-card-actions>
                    <q-btn
                      flat
                      size="sm"
                      color="primary"
                      label="View"
                      @click="$router.push(`/app/ministries/${ministry.id}`)"
                    />
                  </q-card-actions>
                </q-card>
              </div>
            </div>
          </q-card-section>
        </q-card>
      </div>
    </div>

    <!-- Broadcast Message Dialog -->
    <q-dialog v-model="showBroadcastDialog">
      <q-card style="min-width: 500px">
        <q-card-section>
          <div class="text-h6">Broadcast Message</div>
        </q-card-section>

        <q-card-section>
          <q-input
            v-model="broadcastForm.subject"
            label="Subject *"
            outlined
            :rules="[(val) => !!val || 'Subject is required']"
          />

          <q-input
            v-model="broadcastForm.content"
            label="Message *"
            type="textarea"
            outlined
            rows="5"
            class="q-mt-md"
            :rules="[(val) => !!val || 'Message is required']"
          />
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Cancel" v-close-popup />
          <q-btn
            unelevated
            color="primary"
            label="Send Broadcast"
            @click="sendBroadcast"
            :loading="sending"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script setup>
import { ref, onMounted } from 'vue';
// import { useRouter } from 'vue-router';
import { useAuthStore } from 'src/stores/auth';
import {
  dashboardAPI,
  messagesAPI,
} from 'src/services/api';
import { date, Notify } from 'quasar';

// const router = useRouter();
const authStore = useAuthStore();

const announcements = ref([]);
const events = ref([]);
const ministries = ref([]);
const showBroadcastDialog = ref(false);
const sending = ref(false);

const stats = ref({
  totalMembers: 0,
  announcements: 0,
  upcomingEvents: 0,
  ministries: 0,
});

const broadcastForm = ref({
  subject: '',
  content: '',
});

const fetchDashboardData = async () => {
  try {
    const response = await dashboardAPI.getPastorStats();
    const data = response.data.data;

    // Set stats
    stats.value = {
      totalMembers: data.total_members,
      announcements: data.announcements_this_month,
      upcomingEvents: data.upcoming_events,
      ministries: data.total_ministries,
    };

    // Set lists
    announcements.value = data.recent_announcements || [];
    events.value = data.upcoming_events_list || [];
    ministries.value = data.active_ministries_list || [];
  } catch (error) {
    console.error('Error fetching dashboard data:', error);
    Notify.create({
      type: 'negative',
      message: 'Failed to load dashboard data',
    });
  }
};

const sendBroadcast = async () => {
  if (!broadcastForm.value.subject || !broadcastForm.value.content) {
    Notify.create({
      type: 'negative',
      message: 'Please fill in all fields',
    });
    return;
  }

  sending.value = true;
  try {
    await messagesAPI.broadcast(broadcastForm.value);
    Notify.create({
      type: 'positive',
      message: 'Broadcast message sent successfully!',
    });
    showBroadcastDialog.value = false;
    broadcastForm.value = { subject: '', content: '' };
  } catch (error) {
    console.error('Error sending broadcast:', error);
    Notify.create({
      type: 'negative',
      message: 'Failed to send broadcast',
    });
  } finally {
    sending.value = false;
  }
};

const getPriorityColor = (priority) => {
  const colors = { high: 'red', medium: 'orange', low: 'blue' };
  return colors[priority] || 'grey';
};

const formatDate = (dateString) => {
  return date.formatDate(dateString, 'MMM DD, YYYY');
};

const formatDateTime = (dateString) => {
  return date.formatDate(dateString, 'MMM DD, h:mm A');
};

const formatDay = (dateString) => {
  return date.formatDate(dateString, 'DD');
};

onMounted(() => {
  fetchDashboardData();
});
</script>

