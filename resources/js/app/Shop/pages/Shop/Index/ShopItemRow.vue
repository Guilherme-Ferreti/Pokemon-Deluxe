<template>
  <div
    class="flex flex-wrap items-center gap-x-4 gap-y-3 px-4 py-3"
    :data-test="`shop-item-${item.id}-row`"
  >
    <div class="flex min-w-0 flex-[1_1_16rem] items-center gap-3">
      <ItemSprite
        v-if="item.sprite"
        class="size-8 shrink-0"
        :name="item.name"
        :url="item.sprite"
      />
      <div
        v-else
        class="size-8 shrink-0"
        aria-hidden="true"
      />
      <div class="flex min-w-0 flex-col gap-0.5">
        <span class="font-semibold">{{ item.name }}</span>
        <span class="text-sm text-muted-foreground">
          <slot />
        </span>
      </div>
    </div>
    <div
      class="ml-auto flex flex-wrap items-center justify-end gap-x-4 gap-y-2"
    >
      <span class="text-xs text-muted-foreground">
        You own: {{ item.ownedQuantity.toLocaleString('en-US') }}
      </span>
      <MoneyAmount
        class="font-medium"
        :amount="item.price"
        :data-test="`shop-item-${item.id}-row__price-indicator`"
      />
      <ShopItemBuyForm :item="item" />
    </div>
  </div>
</template>

<script setup lang="ts">
import ItemSprite from '@/shared/components/game/ItemSprite.vue';
import MoneyAmount from '@/shared/components/game/MoneyAmount.vue';
import type { BuyableItem } from '../../../types/page-props/ShopIndexPageProps';
import ShopItemBuyForm from './ShopItemBuyForm.vue';

defineProps<{
  item: BuyableItem;
}>();
</script>
