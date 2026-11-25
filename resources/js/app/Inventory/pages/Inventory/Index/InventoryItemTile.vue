<template>
  <button
    type="button"
    class="relative flex cursor-pointer flex-col items-center gap-2 rounded-xl border bg-card px-3 pt-5 pb-4 text-center transition-colors hover:border-ring focus-visible:border-ring focus-visible:ring-3 focus-visible:ring-ring/50 focus-visible:outline-none motion-reduce:transition-none"
    :data-test="`inventory-item-${item.id}-tile`"
  >
    <Badge
      variant="secondary"
      class="absolute top-2 right-2 tabular-nums"
    >
      x{{ item.quantity.toLocaleString('en-US') }}
    </Badge>
    <ItemSprite
      v-if="item.sprite"
      class="size-10"
      :name="item.name"
      :url="item.sprite"
    />
    <div
      v-else
      class="size-10"
      aria-hidden="true"
    />
    <span class="text-sm font-medium">{{ item.name }}</span>
  </button>
</template>

<script setup lang="ts">
import ItemSprite from '@/shared/components/game/ItemSprite.vue';
import { Badge } from '@/shared/components/ui/badge';
import type { OwnedItem } from '../../../types/page-props/InventoryIndexPageProps';

defineProps<{
  item: OwnedItem;
}>();
</script>
