<template>
  <AdminLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">
        Edit User: {{ user.name }}
      </h2>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
          <div class="p-6 bg-white border-b border-gray-200">
            <form @submit.prevent="submit">
              <div class="grid grid-cols-1 gap-6 mt-4 sm:grid-cols-2">
                <!-- Name -->
                <div>
                  <label class="block text-sm font-medium text-gray-700" for="name">
                    Name <span class="text-red-500">*</span>
                  </label>
                  <input
                    id="name"
                    v-model="form.name"
                    type="text"
                    class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                    required
                  />
                  <div v-if="form.errors.name" class="mt-1 text-sm text-red-600">
                    {{ form.errors.name }}
                  </div>
                </div>

                <!-- Email -->
                <div>
                  <label class="block text-sm font-medium text-gray-700" for="email">
                    Email <span class="text-red-500">*</span>
                  </label>
                  <input
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                    required
                  />
                  <div v-if="form.errors.email" class="mt-1 text-sm text-red-600">
                    {{ form.errors.email }}
                  </div>
                </div>

                <!-- Password (Optional) -->
                <div>
                  <label class="block text-sm font-medium text-gray-700" for="password">
                    New Password
                  </label>
                  <input
                    id="password"
                    v-model="form.password"
                    type="password"
                    class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                  />
                  <div v-if="form.errors.password" class="mt-1 text-sm text-red-600">
                    {{ form.errors.password }}
                  </div>
                </div>

                <!-- Password Confirmation -->
                <div>
                  <label class="block text-sm font-medium text-gray-700" for="password_confirmation">
                    Confirm New Password
                  </label>
                  <input
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                  />
                </div>

                <!-- Role -->
                <div>
                  <label class="block text-sm font-medium text-gray-700" for="role">
                    Role <span class="text-red-500">*</span>
                  </label>
                  <select
                    id="role"
                    v-model="form.role"
                    class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                    required
                  >
                    <option value="admin" :selected="user.role === 'admin'">Admin</option>
                    <option value="receptionist" :selected="user.role === 'receptionist'">Receptionist</option>
                    <option value="housekeeping" :selected="user.role === 'housekeeping'">Housekeeping</option>
                    <option value="guest" :selected="user.role === 'guest'">Guest</option>
                  </select>
                  <div v-if="form.errors.role" class="mt-1 text-sm text-red-600">
                    {{ form.errors.role }}
                  </div>
                </div>

                <!-- Phone -->
                <div>
                  <label class="block text-sm font-medium text-gray-700" for="phone">
                    Phone
                  </label>
                  <input
                    id="phone"
                    v-model="form.phone"
                    type="tel"
                    class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                  />
                  <div v-if="form.errors.phone" class="mt-1 text-sm text-red-600">
                    {{ form.errors.phone }}
                  </div>
                </div>

                <!-- Address -->
                <div class="sm:col-span-2">
                  <label class="block text-sm font-medium text-gray-700" for="address">
                    Address
                  </label>
                  <input
                    id="address"
                    v-model="form.address"
                    type="text"
                    class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                  />
                  <div v-if="form.errors.address" class="mt-1 text-sm text-red-600">
                    {{ form.errors.address }}
                  </div>
                </div>

                <!-- City -->
                <div>
                  <label class="block text-sm font-medium text-gray-700" for="city">
                    City
                  </label>
                  <input
                    id="city"
                    v-model="form.city"
                    type="text"
                    class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                  />
                  <div v-if="form.errors.city" class="mt-1 text-sm text-red-600">
                    {{ form.errors.city }}
                  </div>
                </div>

                <!-- State/Province -->
                <div>
                  <label class="block text-sm font-medium text-gray-700" for="state">
                    State/Province
                  </label>
                  <input
                    id="state"
                    v-model="form.state"
                    type="text"
                    class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                  />
                  <div v-if="form.errors.state" class="mt-1 text-sm text-red-600">
                    {{ form.errors.state }}
                  </div>
                </div>

                <!-- Country -->
                <div>
                  <label class="block text-sm font-medium text-gray-700" for="country">
                    Country
                  </label>
                  <input
                    id="country"
                    v-model="form.country"
                    type="text"
                    class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                  />
                  <div v-if="form.errors.country" class="mt-1 text-sm text-red-600">
                    {{ form.errors.country }}
                  </div>
                </div>

                <!-- Postal Code -->
                <div>
                  <label class="block text-sm font-medium text-gray-700" for="postal_code">
                    Postal Code
                  </label>
                  <input
                    id="postal_code"
                    v-model="form.postal_code"
                    type="text"
                    class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                  />
                  <div v-if="form.errors.postal_code" class="mt-1 text-sm text-red-600">
                    {{ form.errors.postal_code }}
                  </div>
                </div>
              </div>

              <div class="flex justify-end mt-6">
                <Link
                  :href="route('admin.users.index')"
                  class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                  Cancel
                </Link>
                <button
                  type="submit"
                  class="inline-flex justify-center px-4 py-2 ml-3 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                  :disabled="form.processing"
                >
                  Update User
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { defineProps } from 'vue';

const props = defineProps({
  user: {
    type: Object,
    required: true,
  },
});

const form = useForm({
  name: props.user.name,
  email: props.user.email,
  password: '',
  password_confirmation: '',
  role: props.user.role,
  phone: props.user.phone || '',
  address: props.user.address || '',
  city: props.user.city || '',
  state: props.user.state || '',
  country: props.user.country || '',
  postal_code: props.user.postal_code || '',
  _method: 'PUT',
});

const submit = () => {
  form.post(route('admin.users.update', props.user.id), {
    onSuccess: () => {
      form.reset('password', 'password_confirmation');
    },
  });
};
</script>
