<template>
  <q-layout view="lHh lpr lFf" class="bg-grey-1">
    <q-page-container>
      <q-page class="flex flex-center">
        <q-card class="q-pa-md shadow-2" style="width: 600px; max-width: 90vw">
          <q-card-section class="text-center">
            <div class="text-h4 text-primary q-mb-sm">
              <q-icon name="church" size="48px" />
            </div>
            <div class="text-h5 text-weight-bold">Join Our Church</div>
            <div class="text-subtitle2 text-grey-7">
              Create your member account
            </div>
          </q-card-section>

          <q-card-section>
            <q-form @submit="handleRegister" class="q-gutter-md">
              <div class="row q-col-gutter-md">
                <!-- Full Name -->
                <div class="col-12">
                  <q-input
                    v-model="form.name"
                    label="Full Name *"
                    outlined
                    :rules="[(val) => !!val || 'Name is required']"
                    prepend-inner-icon="person"
                  />
                </div>

                <!-- Email -->
                <div class="col-12">
                  <q-input
                    v-model="form.email"
                    type="email"
                    label="Email Address *"
                    outlined
                    :rules="[
                      (val) => !!val || 'Email is required',
                      (val) => /.+@.+\..+/.test(val) || 'Invalid email format',
                    ]"
                    prepend-inner-icon="email"
                  />
                </div>

                <!-- Phone -->
                <div class="col-12 col-sm-6">
                  <q-input
                    v-model="form.phone"
                    label="Phone Number"
                    outlined
                    mask="(###) ###-####"
                    prepend-inner-icon="phone"
                  />
                </div>

                <!-- Date of Birth -->
                <div class="col-12 col-sm-6">
                  <q-input
                    v-model="form.date_of_birth"
                    label="Date of Birth"
                    outlined
                    type="date"
                    prepend-inner-icon="cake"
                  />
                </div>

                <!-- Gender -->
                <div class="col-12 col-sm-6">
                  <q-select
                    v-model="form.gender"
                    :options="genderOptions"
                    label="Gender"
                    outlined
                    emit-value
                    map-options
                    prepend-inner-icon="wc"
                  />
                </div>

                <!-- Marital Status -->
                <div class="col-12 col-sm-6">
                  <q-select
                    v-model="form.marital_status"
                    :options="maritalOptions"
                    label="Marital Status"
                    outlined
                    emit-value
                    map-options
                    prepend-inner-icon="favorite"
                  />
                </div>

                <!-- Address -->
                <div class="col-12">
                  <q-input
                    v-model="form.address"
                    label="Address"
                    outlined
                    prepend-inner-icon="home"
                  />
                </div>

                <!-- City -->
                <div class="col-12 col-sm-6">
                  <q-input
                    v-model="form.city"
                    label="City"
                    outlined
                    prepend-inner-icon="location_city"
                  />
                </div>

                <!-- State -->
                <div class="col-12 col-sm-3">
                  <q-input
                    v-model="form.state"
                    label="State"
                    outlined
                    prepend-inner-icon="map"
                  />
                </div>

                <!-- Zip Code -->
                <div class="col-12 col-sm-3">
                  <q-input
                    v-model="form.zip_code"
                    label="Zip Code"
                    outlined
                    mask="#####"
                  />
                </div>

                <!-- Occupation -->
                <div class="col-12">
                  <q-input
                    v-model="form.occupation"
                    label="Occupation"
                    outlined
                    prepend-inner-icon="work"
                  />
                </div>

                <!-- Emergency Contact -->
                <div class="col-12">
                  <q-input
                    v-model="form.emergency_contact"
                    label="Emergency Contact (Name & Phone)"
                    outlined
                    prepend-inner-icon="contact_phone"
                    placeholder="e.g., John Doe - (555) 123-4567"
                  />
                </div>

                <!-- Password -->
                <div class="col-12 col-sm-6">
                  <q-input
                    v-model="form.password"
                    :type="showPassword ? 'text' : 'password'"
                    label="Password *"
                    outlined
                    :rules="[
                      (val) => !!val || 'Password is required',
                      (val) =>
                        val.length >= 6 ||
                        'Password must be at least 6 characters',
                    ]"
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
                </div>

                <!-- Confirm Password -->
                <div class="col-12 col-sm-6">
                  <q-input
                    v-model="form.password_confirmation"
                    :type="showPassword ? 'text' : 'password'"
                    label="Confirm Password *"
                    outlined
                    :rules="[
                      (val) => !!val || 'Please confirm password',
                      (val) =>
                        val === form.password || 'Passwords do not match',
                    ]"
                    prepend-inner-icon="lock"
                  />
                </div>
              </div>

              <div class="q-mt-md">
                <q-btn
                  type="submit"
                  label="Create Account"
                  color="primary"
                  class="full-width"
                  size="lg"
                  :loading="authStore.loading"
                  icon="person_add"
                />
              </div>

              <div class="text-center q-mt-md">
                <router-link to="/login" class="text-primary">
                  Already have an account? Sign in here
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
  name: '',
  email: '',
  phone: '',
  date_of_birth: '',
  gender: '',
  address: '',
  city: '',
  state: '',
  zip_code: '',
  country: 'USA',
  marital_status: '',
  occupation: '',
  emergency_contact: '',
  password: '',
  password_confirmation: '',
});

const showPassword = ref(false);

const genderOptions = [
  { label: 'Male', value: 'male' },
  { label: 'Female', value: 'female' },
  { label: 'Other', value: 'other' },
];

const maritalOptions = [
  { label: 'Single', value: 'single' },
  { label: 'Married', value: 'married' },
  { label: 'Divorced', value: 'divorced' },
  { label: 'Widowed', value: 'widowed' },
];

const handleRegister = async () => {
  const success = await authStore.register(form.value);
  if (success) {
    router.push('/app/dashboard');
  }
};
</script>

