<template>
  <Card
    class="h-full min-h-68 justify-between gap-4 p-4 transition-shadow hover:shadow-md"
  >
    <div class="flex items-center justify-between gap-2">
      <p class="text-xs font-bold text-muted-foreground">
        #{{ eggIncubatorIndex }}
      </p>
      <Badge variant="secondary">
        <component :is="badgeIcon" />
        <span>{{ badgeText }}</span>
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
import type { EggIncubation } from '@/shared/types/response';

defineProps<{
  eggIncubatorIndex: EggIncubation['eggIncubatorIndex'];
  badgeIcon: Icon;
  badgeText: string;
}>();
</script>
