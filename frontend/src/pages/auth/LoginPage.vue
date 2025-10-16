<template>
  <q-layout view="lHh lpr lFf" class="bg-grey-1">
    <q-page-container>
      <q-page class="flex flex-center">
        <q-card class="q-pa-md shadow-2" style="width: 400px">
          <q-card-section class="text-center">
            <div class="text-h4 text-primary q-mb-sm">
              <q-icon name="church" size="48px" />
            </div>
            <div class="text-h5 text-weight-bold">Church Management</div>
            <div class="text-subtitle2 text-grey-7">Sign in to continue</div>
          </q-card-section>

          <q-card-section>
            <q-form @submit="handleLogin" class="q-gutter-md">
              <q-input
                v-model="form.email"
                type="email"
                label="Email"
                outlined
                :rules="[(val) => !!val || 'Email is required']"
                prepend-inner-icon="email"
              />

              <q-input
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                label="Password"
                outlined
                :rules="[(val) => !!val || 'Password is required']"
                prepend-inner-icon="lock"
              >
                <template v-slot:append>
                  <q-icon
                    :name="showPassword ? 'visibility_off' : 'visibility'"
                    class="cursor-pointer"
                    @click="showPassword = !showPassword"
                  />
                </template>
              </q-input>

              <q-btn
                type="submit"
                label="Sign In"
                color="primary"
                class="full-width"
                size="lg"
                :loading="authStore.loading"
              />

              <div class="text-center q-mt-md">
                <router-link to="/register" class="text-primary">
                  Don't have an account? Register here
                </router-link>
              </div>
            </q-form>
          </q-card-section>
        </q-card>
      </q-page>
    </q-page-container>
  </q-layout>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from 'src/stores/auth';

const router = useRouter();
const authStore = useAuthStore();

const form = ref({
  email: '',
  password: '',
});

const showPassword = ref(false);

const handleLogin = async () => {
  const success = await authStore.login(form.value);
  if (success) {
    router.push('/app/dashboard');
  }
};
</script>

