<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { IconChevronRight } from '@tabler/icons-vue';
import { cn } from '@/lib/utils';
import { Button } from '@/components/ui/button';
import { useSidebar } from './utils';

const props = defineProps<{
  class?: HTMLAttributes['class'];
}>();

const { toggleSidebar, open, isMobile } = useSidebar();
</script>

<template>
  <Button
    data-sidebar="trigger"
    data-slot="sidebar-trigger"
    variant="secondary"
    size="icon"
    :class="cn('h-7 w-7', props.class)"
    @click="toggleSidebar"
  >
    <IconChevronRight
      :class="{
        'size-3 transform': true,
        'rotate-180': open && !isMobile,
      }"
    />
    <span class="sr-only">Toggle Sidebar</span>
  </Button>
</template>
