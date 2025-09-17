<template>
  <div v-if="links.length > 3">
    <div class="flex flex-wrap -mb-1">
      <template v-for="(link, key) in links" :key="key">
        <div v-if="link.url === null" 
             class="px-4 py-2 mb-1 mr-1 text-sm text-gray-400 border rounded"
             v-html="link.label"
        />
        <Link v-else
              class="px-4 py-2 mb-1 mr-1 text-sm border rounded hover:bg-white focus:border-indigo-500 focus:text-indigo-500"
              :class="{ 'bg-white': link.active }"
              :href="link.url"
              v-html="link.label"
        />
      </template>
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
  links: {
    type: Array,
    required: true,
  },
});
</script>
