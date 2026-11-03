<template>
  <Card
    class="h-full min-h-68 justify-between gap-4 p-4 transition-shadow hover:shadow-md"
  >
    <div class="flex items-center justify-between gap-2">
      <p class="text-xs font-bold text-muted-foreground">#{{ index }}</p>
      <Badge variant="secondary">
        <component :is="badge.icon" />
        <span>{{ badge.text }}</span>
      </Badge>
    </div>
    <slot name="picture" />
    <slot />
    <slot name="footer" />
  </Card>
</template>

<script setup lang="ts">
import type { Icon } from '@tabler/icons-vue';
import { Badge } from '@/shared/components/ui/badge';
import { Card } from '@/shared/components/ui/card';
import type { Incubator } from '../../types/page-props/NurseryPageProps';

export interface IncubatorCardProps {
  index: Incubator['index'];
  badge: {
    icon: Icon;
    text: string;
  };
}

defineProps<IncubatorCardProps>();
</script>
