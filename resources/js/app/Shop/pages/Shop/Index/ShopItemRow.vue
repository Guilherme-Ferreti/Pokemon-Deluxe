<template>
  <div class="flex flex-wrap items-center gap-x-4 gap-y-3 px-4 py-3">
    <div class="flex min-w-0 flex-[1_1_16rem] items-center gap-3">
      <ItemSprite
        v-if="sprite"
        class="size-8 shrink-0"
        :name="name"
        :url="sprite"
      />
      <div class="flex min-w-0 flex-col gap-0.5">
        <span class="font-semibold">{{ name }}</span>
        <span class="text-sm text-muted-foreground">
          <slot />
        </span>
      </div>
    </div>
    <div class="ml-auto flex items-center gap-4">
      <span class="text-xs text-muted-foreground">
        You own: {{ ownedQuantity.toLocaleString('en-US') }}
      </span>
      <MoneyAmount
        class="font-medium"
        :amount="price"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import ItemSprite from '@/shared/components/game/ItemSprite.vue';
import MoneyAmount from '@/shared/components/game/MoneyAmount.vue';

defineProps<{
  name: string;
  sprite: string | null;
  price: number;
  ownedQuantity: number;
}>();
</script>
