<template>
  <component :is="dashboardComponent" />
</template>

<script setup>
import { computed } from 'vue';
import { useAuthStore } from 'src/stores/auth';
import UserDashboard from './UserDashboard.vue';
import PastorDashboard from './PastorDashboard.vue';
import FinanceDashboard from './FinanceDashboard.vue';
import AdminDashboard from './AdminDashboard.vue';

const authStore = useAuthStore();

const dashboardComponent = computed(() => {
  // Priority: administrator > pastor > finance_committee > user
  if (authStore.isAdministrator) {
    return AdminDashboard;
  } else if (authStore.isPastor) {
    return PastorDashboard;
  } else if (authStore.isFinanceCommittee) {
    return FinanceDashboard;
  } else {
    return UserDashboard;
  }
});
</script>

