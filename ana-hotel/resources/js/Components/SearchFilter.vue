<template>
  <div class="flex items-center">
    <div class="relative flex-1">
      <input
        type="text"
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
        class="block w-full pr-10 text-sm border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
        placeholder="Search..."
      />
      <div v-if="modelValue" class="absolute inset-y-0 right-0 flex items-center pr-3">
        <button
          type="button"
          @click="$emit('update:modelValue', '')"
          class="text-gray-400 hover:text-gray-500 focus:outline-none"
        >
          <span class="sr-only">Clear search</span>
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
    </div>
    <button
      v-if="hasFilters"
      type="button"
      @click="$emit('reset')"
      class="ml-2 text-sm text-gray-600 hover:text-gray-800 focus:outline-none"
    >
      Reset
    </button>
  </div>
</template>

<script setup>
import { computed } from 'vue';

defineProps({
  modelValue: {
    type: String,
    default: '',
  },
  hasFilters: {
    type: Boolean,
    default: false,
  },
});

defineEmits(['update:modelValue', 'reset']);
</script>
