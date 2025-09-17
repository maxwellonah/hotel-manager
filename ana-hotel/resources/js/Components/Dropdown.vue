<template>
  <div class="relative">
    <div @click="open = !open">
      <slot name="trigger" />
    </div>

    <!-- Full Screen Dropdown Overlay -->
    <div v-show="open" class="fixed inset-0 z-40" @click="open = false" />

    <transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="transform scale-95 opacity-0"
      enter-to-class="transform scale-100 opacity-100"
      leave-active-class="transition duration-75 ease-in"
      leave-from-class="transform scale-100 opacity-100"
      leave-to-class="transform scale-95 opacity-0"
    >
      <div
        v-show="open"
        class="absolute z-50 mt-2 rounded-md shadow-lg"
        :class="[widthClass, alignmentClasses]"
        style="display: none;"
        @click="open = false"
      >
        <div
          class="ring-1 ring-black ring-opacity-5 rounded-md bg-white shadow-xs"
          :class="contentClasses"
        >
          <slot name="content" />
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue';

const props = defineProps({
  align: {
    type: String,
    default: 'right',
  },
  width: {
    type: String,
    default: '48',
  },
  contentClasses: {
    type: Array,
    default: () => ['py-1', 'bg-white'],
  },
  closeOnClick: {
    type: Boolean,
    default: true,
  },
});

const open = ref(false);

const closeOnEscape = (e) => {
  if (open.value && e.key === 'Escape') {
    open.value = false;
  }
};

onMounted(() => document.addEventListener('keydown', closeOnEscape));
onUnmounted(() => {
  document.removeEventListener('keydown', closeOnEscape);
  document.removeEventListener('click', closeOnClickOutside);
});

const closeOnClickOutside = (event) => {
  if (!event.target.closest('.relative')) {
    open.value = false;
  }
};

watch(open, (value) => {
  if (value) {
    document.addEventListener('click', closeOnClickOutside);
  } else {
    document.removeEventListener('click', closeOnClickOutside);
  }
});

const widthClass = {
  '48': 'w-48',
  '60': 'w-60',
  '72': 'w-72',
  '96': 'w-96',
  'auto': 'w-auto',
  'full': 'w-full',
}[props.width.toString()];

const alignmentClasses = {
  left: 'origin-top-left left-0',
  right: 'origin-top-right right-0',
  top: 'origin-top',
  bottom: 'origin-bottom',
  'top-left': 'origin-top-left left-0',
  'top-right': 'origin-top-right right-0',
  'bottom-left': 'origin-bottom-left left-0',
  'bottom-right': 'origin-bottom-right right-0',
}[props.align];
</script>
