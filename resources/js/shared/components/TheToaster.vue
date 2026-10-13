<template>
  <Toaster
    position="bottom-right"
    class="the-toaster"
    :theme="mode === 'dark' ? 'dark' : 'light'"
    :expand="true"
    :duration="7000"
  />
</template>

<script setup lang="ts">
import 'vue-sonner/style.css';
import { router } from '@inertiajs/vue3';
import { useColorMode } from '@vueuse/core';
import { toast } from 'vue-sonner';
import { Toaster } from '@/shared/components/ui/sonner';

const mode = useColorMode();

router.on('flash', (event) => {
  if (!event.detail.flash.toast) {
    return;
  }

  switch (event.detail.flash.toast.type) {
    case 'success':
      toast.success(event.detail.flash.toast.title, {
        description: event.detail.flash.toast.message,
      });
      break;
    case 'error':
      toast.error(event.detail.flash.toast.title, {
        description: event.detail.flash.toast.message,
      });
      break;
    case 'info':
      toast.info(event.detail.flash.toast.title, {
        description: event.detail.flash.toast.message,
      });
      break;
    case 'warning':
      toast.warning(event.detail.flash.toast.title, {
        description: event.detail.flash.toast.message,
      });
      break;
  }
});

router.on('networkError', () => {
  toast.error('Network error', {
    description: 'Please check your internet connection and try again.',
  });
});
</script>

<style>
@reference '@css/app.css';

.the-toaster [data-sonner-toast] {
  @apply overflow-y-hidden;

  &::before {
    @apply absolute left-0 h-full w-1 content-[''];
  }

  &[data-type='success'] {
    &::before {
      @apply bg-(--success-text);
    }

    & [data-icon],
    [data-title] {
      @apply text-(--success-text);
    }
  }

  &[data-type='error'] {
    &::before {
      @apply bg-(--error-text);
    }

    & [data-icon],
    [data-title] {
      @apply text-(--error-text);
    }
  }

  &[data-type='info'] {
    &::before {
      @apply bg-(--info-text);
    }

    & [data-icon],
    [data-title] {
      @apply text-(--info-text);
    }
  }

  &[data-type='warning'] {
    &::before {
      @apply bg-(--warning-text);
    }

    & [data-icon],
    [data-title] {
      @apply text-(--warning-text);
    }
  }
}
</style>
