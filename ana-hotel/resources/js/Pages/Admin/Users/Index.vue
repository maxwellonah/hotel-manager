<template>
  <AdminLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">
        User Management
      </h2>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
          <div class="p-6 bg-white border-b border-gray-200">
            <div class="flex items-center justify-between mb-6">
              <SearchFilter v-model="form.search" class="w-full max-w-md mr-4" @reset="reset">
                <label class="block text-sm font-medium text-gray-700">Role</label>
                <select v-model="form.role" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                  <option :value="null" />
                  <option value="admin">Admin</option>
                  <option value="receptionist">Receptionist</option>
                  <option value="housekeeping">Housekeeping</option>
                  <option value="guest">Guest</option>
                </select>
              </SearchFilter>
              <Link :href="route('admin.users.create')" class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase bg-indigo-600 rounded-md border border-transparent hover:bg-indigo-500">
                Create User
              </Link>
            </div>

            <div class="mb-4 overflow-x-auto bg-white rounded-md shadow">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                      <Link :href="sortUrl('name')" class="group inline-flex">
                        Name
                        <span v-if="filters.sort === 'name'" class="flex-shrink-0 ml-1 text-gray-400">
                          <ChevronUpIcon v-if="filters.direction === 'asc'" class="w-4 h-4" />
                          <ChevronDownIcon v-else class="w-4 h-4" />
                        </span>
                      </Link>
                    </th>
                    <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                      <Link :href="sortUrl('email')" class="group inline-flex">
                        Email
                        <span v-if="filters.sort === 'email'" class="flex-shrink-0 ml-1 text-gray-400">
                          <ChevronUpIcon v-if="filters.direction === 'asc'" class="w-4 h-4" />
                          <ChevronDownIcon v-else class="w-4 h-4" />
                        </span>
                      </Link>
                    </th>
                    <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                      Role
                    </th>
                    <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                      <Link :href="sortUrl('created_at')" class="group inline-flex">
                        Joined
                        <span v-if="filters.sort === 'created_at'" class="flex-shrink-0 ml-1 text-gray-400">
                          <ChevronUpIcon v-if="filters.direction === 'asc'" class="w-4 h-4" />
                          <ChevronDownIcon v-else class="w-4 h-4" />
                        </span>
                      </Link>
                    </th>
                    <th scope="col" class="relative px-6 py-3">
                      <span class="sr-only">Actions</span>
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="user in users.data" :key="user.id" class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="flex items-center">
                        <div class="ml-4">
                          <div class="text-sm font-medium text-gray-900">
                            {{ user.name }}
                          </div>
                        </div>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm text-gray-900">{{ user.email }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span :class="roleBadgeClass(user.role)">
                        {{ user.role }}
                      </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                      {{ user.created_at }}
                    </td>
                    <td class="px-6 py-4 text-sm font-medium text-right whitespace-nowrap">
                      <Link :href="route('admin.users.edit', user.id)" class="text-indigo-600 hover:text-indigo-900">
                        Edit
                      </Link>
                      <button @click="confirmUserDeletion(user)" class="ml-4 text-red-600 hover:text-red-900">
                        Delete
                      </button>
                    </td>
                  </tr>
                  <tr v-if="users.data.length === 0">
                    <td colspan="5" class="px-6 py-4 text-sm text-center text-gray-500">
                      No users found.
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <Pagination :links="users.links" class="mt-4" />
          </div>
        </div>
      </div>
    </div>

    <!-- Delete User Confirmation Modal -->
    <ConfirmationModal :show="confirmingUserDeletion" @close="closeModal">
      <template #title>
        Delete User
      </template>

      <template #content>
        Are you sure you want to delete this user? This action cannot be undone.
      </template>

      <template #footer>
        <SecondaryButton @click="closeModal">
          Cancel
        </SecondaryButton>

        <DangerButton
          class="ml-3"
          :class="{ 'opacity-25': form.processing }"
          :disabled="form.processing"
          @click="deleteUser"
        >
          Delete User
        </DangerButton>
      </template>
    </ConfirmationModal>
  </AdminLayout>
</template>

<script setup>
import { ref, reactive, watch } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { ChevronUpIcon, ChevronDownIcon } from '@heroicons/vue/20/solid';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import SearchFilter from '@/Components/SearchFilter.vue';
import Pagination from '@/Components/Pagination.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';

const props = defineProps({
  users: Object,
  filters: {
    type: Object,
    default: () => ({
      search: '',
      role: '',
      sort: 'created_at',
      direction: 'desc',
    }),
  },
});

const form = reactive({
  search: props.filters.search,
  role: props.filters.role,
});

const confirmingUserDeletion = ref(false);
const userToDelete = ref(null);

const roleBadgeClass = (role) => {
  const classes = {
    'px-2 inline-flex text-xs leading-5 font-semibold rounded-full': true,
    'bg-green-100 text-green-800': role === 'admin',
    'bg-blue-100 text-blue-800': role === 'receptionist',
    'bg-yellow-100 text-yellow-800': role === 'housekeeping',
    'bg-gray-100 text-gray-800': role === 'guest',
  };
  return classes;
};

const sortUrl = (column) => {
  const direction = props.filters.sort === column && props.filters.direction === 'asc' ? 'desc' : 'asc';
  return route('admin.users.index', {
    ...route().params,
    sort: column,
    direction,
  });
};

const reset = () => {
  form.search = '';
  form.role = '';
};

const confirmUserDeletion = (user) => {
  userToDelete.value = user;
  confirmingUserDeletion.value = true;
};

const deleteUser = () => {
  if (userToDelete.value) {
    router.delete(route('admin.users.destroy', userToDelete.value.id), {
      preserveScroll: true,
      onSuccess: () => {
        closeModal();
      },
    });
  }
};

const closeModal = () => {
  confirmingUserDeletion.value = false;
  userToDelete.value = null;
};

// Watch for changes in the form and reload the page with the new filters
watch(
  () => ({ ...form }),
  () => {
    router.get(route('admin.users.index'), {
      search: form.search,
      role: form.role,
      sort: props.filters.sort,
      direction: props.filters.direction,
    }, {
      preserveState: true,
      replace: true,
      preserveScroll: true,
    });
  },
  { deep: true, immediate: false }
);
</script>
