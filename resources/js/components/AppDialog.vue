<template>
  <Dialog>
    <DialogTrigger>
      <slot name="trigger" />
    </DialogTrigger>
    <DialogContent
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
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import {
  Dialog,
  DialogContent,
  DialogDescription,
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
</script>
