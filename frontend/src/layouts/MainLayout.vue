<template>
  <q-layout view="lHh Lpr lFf">
    <!-- Header -->
    <q-header elevated class="bg-primary text-white">
      <q-toolbar>
        <q-btn
          flat
          dense
          round
          icon="menu"
          aria-label="Menu"
          @click="toggleLeftDrawer"
        />

        <q-toolbar-title>
          <q-icon name="church" size="sm" class="q-mr-sm" />
          Church Management System
        </q-toolbar-title>

        <!-- User Menu -->
        <q-btn flat round dense icon="account_circle">
          <q-menu>
            <q-list style="min-width: 200px">
              <q-item>
                <q-item-section avatar>
                  <q-avatar color="primary" text-color="white" icon="person" />
                </q-item-section>
                <q-item-section>
                  <q-item-label>{{ authStore.user?.name }}</q-item-label>
                  <q-item-label caption>{{ authStore.user?.email }}</q-item-label>
                </q-item-section>
              </q-item>

              <q-separator />

              <q-item clickable v-close-popup @click="$router.push('/app/profile')">
                <q-item-section avatar>
                  <q-icon name="settings" />
                </q-item-section>
                <q-item-section>Profile Settings</q-item-section>
              </q-item>

              <q-item clickable v-close-popup @click="handleLogout">
                <q-item-section avatar>
                  <q-icon name="logout" />
                </q-item-section>
                <q-item-section>Logout</q-item-section>
              </q-item>
            </q-list>
          </q-menu>
        </q-btn>
      </q-toolbar>
    </q-header>

    <!-- Sidebar / Drawer -->
    <q-drawer
      v-model="leftDrawerOpen"
      show-if-above
      bordered
      class="bg-grey-1"
    >
      <!-- User Info -->
      <div class="q-pa-md bg-primary text-white">
        <div class="text-h6">{{ authStore.user?.name }}</div>
        <div class="text-caption">{{ getRoleDisplay() }}</div>
      </div>

      <!-- Navigation Menu -->
      <q-list>
        <q-item-label header class="text-grey-8">MAIN MENU</q-item-label>

        <!-- Dashboard -->
        <q-item
          clickable
          v-ripple
          :active="isActive('/app/dashboard')"
          @click="$router.push('/app/dashboard')"
        >
          <q-item-section avatar>
            <q-icon name="dashboard" />
          </q-item-section>
          <q-item-section>Dashboard</q-item-section>
        </q-item>

        <!-- Announcements -->
        <q-item
          clickable
          v-ripple
          :active="isActive('/app/announcements')"
          @click="$router.push('/app/announcements')"
        >
          <q-item-section avatar>
            <q-icon name="campaign" />
          </q-item-section>
          <q-item-section>Announcements</q-item-section>
        </q-item>

        <!-- Events -->
        <q-item
          clickable
          v-ripple
          :active="isActive('/app/events')"
          @click="$router.push('/app/events')"
        >
          <q-item-section avatar>
            <q-icon name="event" />
          </q-item-section>
          <q-item-section>Events</q-item-section>
        </q-item>

        <!-- Messages -->
        <q-item
          clickable
          v-ripple
          :active="isActive('/app/messages')"
          @click="$router.push('/app/messages')"
        >
          <q-item-section avatar>
            <q-icon name="mail" />
          </q-item-section>
          <q-item-section>Messages</q-item-section>
        </q-item>

        <!-- Ministries -->
        <q-item
          clickable
          v-ripple
          :active="isActive('/app/ministries')"
          @click="$router.push('/app/ministries')"
        >
          <q-item-section avatar>
            <q-icon name="groups" />
          </q-item-section>
          <q-item-section>Ministries</q-item-section>
        </q-item>

        <!-- Contributions (Finance Committee & Admin) -->
        <q-item
          v-if="authStore.isFinanceCommittee || authStore.isAdministrator"
          clickable
          v-ripple
          :active="isActive('/app/contributions')"
          @click="$router.push('/app/contributions')"
        >
          <q-item-section avatar>
            <q-icon name="payments" />
          </q-item-section>
          <q-item-section>Contributions</q-item-section>
        </q-item>

        <!-- Admin Section -->
        <template v-if="authStore.isAdministrator">
          <q-separator class="q-my-md" />
          <q-item-label header class="text-grey-8">ADMINISTRATION</q-item-label>

          <q-item
            clickable
            v-ripple
            :active="isActive('/app/users')"
            @click="$router.push('/app/users')"
          >
            <q-item-section avatar>
              <q-icon name="people" />
            </q-item-section>
            <q-item-section>User Management</q-item-section>
          </q-item>

          <q-item
            clickable
            v-ripple
            :active="isActive('/app/reports')"
            @click="$router.push('/app/reports')"
          >
            <q-item-section avatar>
              <q-icon name="assessment" />
            </q-item-section>
            <q-item-section>Reports</q-item-section>
          </q-item>

          <q-item
            clickable
            v-ripple
            :active="isActive('/app/settings')"
            @click="$router.push('/app/settings')"
          >
            <q-item-section avatar>
              <q-icon name="settings" />
            </q-item-section>
            <q-item-section>System Settings</q-item-section>
          </q-item>
        </template>
      </q-list>
    </q-drawer>

    <!-- Page Content -->
    <q-page-container>
      <router-view />
    </q-page-container>
  </q-layout>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from 'src/stores/auth'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()

const leftDrawerOpen = ref(false)

function toggleLeftDrawer() {
  leftDrawerOpen.value = !leftDrawerOpen.value
}

function isActive(path) {
  return route.path.startsWith(path)
}

function getRoleDisplay() {
  const role = authStore.primaryRole
  const roleMap = {
    administrator: 'Administrator',
    pastor: 'Pastor',
    finance_committee: 'Finance Committee',
    user: 'Member',
  }
  return roleMap[role] || 'Member'
}

async function handleLogout() {
  await authStore.logout()
  router.push('/login')
}
</script>
