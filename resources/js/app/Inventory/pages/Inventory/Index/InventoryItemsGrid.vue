<template>
  <div data-test="inventory-items-grid">
    <div
      v-if="items.length > 0"
      class="grid grid-cols-[repeat(auto-fill,minmax(8rem,1fr))] gap-4"
    >
      <InventoryItemTile
        v-for="item in items"
        :key="item.id"
        :item="item"
        @click="selectedItem = item"
      />
    </div>
    <Card v-else>
      <AppEmptyState
        data-test="inventory-items-grid__empty-state"
        :icon="IconBackpack"
        :title="emptyTitle"
        :description="emptyDescription"
      >
        <Button
          v-if="shopTab"
          as-child
        >
          <Link
            data-test="inventory-items-grid__shop-link"
            :href="`${route('shop.web.index')}?tab=${shopTab}`"
          >
            Go to the Shop
          </Link>
        </Button>
      </AppEmptyState>
    </Card>
    <InventoryItemDialog
      :item="selectedItem"
      @close="selectedItem = null"
    />
  </div>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { IconBackpack } from '@tabler/icons-vue';
import { ref } from 'vue';
import AppEmptyState from '@/shared/components/content/AppEmptyState.vue';
import { Button } from '@/shared/components/ui/button';
import { Card } from '@/shared/components/ui/card';
import { route } from '@/shared/types/helpers/route';
import type { OwnedItem } from '../../../types/page-props/InventoryIndexPageProps';
import InventoryItemDialog from './InventoryItemDialog.vue';
import InventoryItemTile from './InventoryItemTile.vue';

defineProps<{
  items: OwnedItem[];
  emptyTitle: string;
  emptyDescription: string;
  shopTab?: string;
}>();

const selectedItem = ref<OwnedItem | null>(null);
</script>
