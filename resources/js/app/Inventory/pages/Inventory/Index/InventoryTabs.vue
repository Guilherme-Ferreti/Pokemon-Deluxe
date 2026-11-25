<template>
  <Tabs
    v-model="selectedTab"
    class="gap-4"
    data-test="inventory-tabs"
  >
    <TabsList class="h-auto max-w-full flex-wrap justify-start">
      <TabsTrigger
        value="balls"
        data-test="inventory-tabs__balls-button"
      >
        Balls
      </TabsTrigger>
      <TabsTrigger
        value="evolution-items"
        data-test="inventory-tabs__evolution-items-button"
      >
        Evolution items
      </TabsTrigger>
      <TabsTrigger
        value="tms"
        data-test="inventory-tabs__tms-button"
      >
        TMs
      </TabsTrigger>
      <TabsTrigger
        value="boost-stones"
        data-test="inventory-tabs__boost-stones-button"
      >
        Boost stones
      </TabsTrigger>
      <TabsTrigger
        value="key-items"
        data-test="inventory-tabs__key-items-button"
      >
        Key items
      </TabsTrigger>
    </TabsList>
    <TabsContent value="balls">
      <InventoryItemsGrid
        empty-title="No balls yet"
        empty-description="Buy them in the Shop or earn them from missions."
        shop-tab="balls"
        :items="page.props.pokeballs"
      />
    </TabsContent>
    <TabsContent value="evolution-items">
      <InventoryItemsGrid
        empty-title="No evolution items yet"
        empty-description="Buy them in the Shop or earn them from missions."
        shop-tab="evolution-items"
        :items="page.props.evolutionItems"
      />
    </TabsContent>
    <TabsContent value="tms">
      <InventoryItemsGrid
        empty-title="No TMs yet"
        empty-description="Buy them in the Shop or earn them from missions."
        shop-tab="tms"
        :items="page.props.tms"
      />
    </TabsContent>
    <TabsContent value="boost-stones">
      <InventoryItemsGrid
        empty-title="No boost stones yet"
        empty-description="Earn them from missions."
        :items="page.props.boostStones"
      />
    </TabsContent>
    <TabsContent value="key-items">
      <InventoryItemsGrid
        empty-title="No key items yet"
        empty-description="Earn them from missions."
        :items="page.props.keyItems"
      />
    </TabsContent>
  </Tabs>
</template>

<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import {
  Tabs,
  TabsContent,
  TabsList,
  TabsTrigger,
} from '@/shared/components/ui/tabs';
import { useTabInUrl } from '@/shared/composables/useTabInUrl';
import type { InventoryIndexPageProps } from '../../../types/page-props/InventoryIndexPageProps';
import InventoryItemsGrid from './InventoryItemsGrid.vue';

const page = usePage<InventoryIndexPageProps>();

const selectedTab = useTabInUrl(
  ['balls', 'evolution-items', 'tms', 'boost-stones', 'key-items'],
  'balls',
);
</script>
