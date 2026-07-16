<template>
  <Dialog @update:open="(open) => (open ? $emit('open') : $emit('close'))">
    <DialogTrigger as="div">
      <slot name="trigger" />
    </DialogTrigger>
    <DialogContent
      class="max-h-[calc(100vh-2rem)] overflow-auto"
      :class="{
        'sm:max-w-md': size === 'sm',
        'sm:max-w-lg': size === 'md',
        'sm:max-w-xl': size === 'lg',
        'sm:max-w-2xl': size === 'xl',
      }"
    >
      <DialogHeader>
        <DialogTitle>{{ title }}</DialogTitle>
        <DialogDescription v-if="description">
          {{ description }}
        </DialogDescription>
      </DialogHeader>
      <slot />
      <DialogFooter class="mt-4">
        <slot name="footer" />
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from '@/components/ui/dialog';

withDefaults(
  defineProps<{
    title: string;
    description?: string;
    size?: 'sm' | 'md' | 'lg' | 'xl';
  }>(),
  {
    size: 'md',
  },
);

defineEmits<{
  open: [];
  close: [];
}>();
</script>
