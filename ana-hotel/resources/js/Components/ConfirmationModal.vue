<template>
  <Modal :show="show" @close="close">
    <div class="px-6 py-4">
      <div class="text-lg font-medium text-gray-900">
        <slot name="title" />
      </div>

      <div class="mt-4 text-sm text-gray-600">
        <slot name="content" />
      </div>
    </div>

    <div class="flex flex-row justify-end px-6 py-4 bg-gray-100 text-right">
      <slot name="footer" />
    </div>
  </Modal>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
  show: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['close']);

const close = () => {
  emit('close');
};
</script>
