<script setup lang="ts">
import { IconChevronLeft } from '@tabler/icons-vue';
import type { HTMLAttributes } from 'vue';
import { Button } from '@/shared/components/ui/button';
import { useSidebar } from '@/shared/components/ui/sidebar/utils';
import { cn } from '@/shared/lib/utils';

const props = defineProps<{
  class?: HTMLAttributes['class'];
}>();

const { toggleSidebar } = useSidebar();
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
    <IconChevronLeft class="size-3" />
    <span class="sr-only">Toggle Sidebar</span>
  </Button>
</template>
