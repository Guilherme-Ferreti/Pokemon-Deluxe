<template>
  <Tabs
    v-model="selectedTab"
    class="gap-4"
    data-test="shop-tabs"
  >
    <TabsList>
      <TabsTrigger
        value="balls"
        data-test="shop-tabs__balls-button"
      >
        Balls
      </TabsTrigger>
      <TabsTrigger
        value="evolution-items"
        data-test="shop-tabs__evolution-items-button"
      >
        Evolution items
      </TabsTrigger>
      <TabsTrigger
        value="tms"
        data-test="shop-tabs__tms-button"
      >
        Today's TMs
      </TabsTrigger>
    </TabsList>
    <TabsContent value="balls">
      <ShopItemsList>
        <ShopItemRow
          v-for="pokeball in page.props.pokeballs"
          :key="pokeball.id"
          :item="pokeball"
        >
          {{ pokeball.effect }}
        </ShopItemRow>
      </ShopItemsList>
    </TabsContent>
    <TabsContent value="evolution-items">
      <ShopItemsList>
        <ShopItemRow
          v-for="evolutionItem in page.props.evolutionItems"
          :key="evolutionItem.id"
          :item="evolutionItem"
        >
          {{ evolutionItem.effect }}
        </ShopItemRow>
      </ShopItemsList>
    </TabsContent>
    <TabsContent value="tms">
      <ShopItemsList>
        <ShopItemRow
          v-for="tm in page.props.tms"
          :key="tm.id"
          :item="tm"
        >
          <span class="flex items-center gap-2">
            <PokemonTypeBadges :types="[tm.type]" />
            <span>Power {{ tm.power ?? '—' }}</span>
          </span>
        </ShopItemRow>
      </ShopItemsList>
    </TabsContent>
  </Tabs>
</template>

<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import PokemonTypeBadges from '@/shared/components/game/PokemonTypeBadges.vue';
import {
  Tabs,
  TabsContent,
  TabsList,
  TabsTrigger,
} from '@/shared/components/ui/tabs';
import { useTabInUrl } from '@/shared/composables/useTabInUrl';
import type { ShopIndexPageProps } from '../../../types/page-props/ShopIndexPageProps';
import ShopItemRow from './ShopItemRow.vue';
import ShopItemsList from './ShopItemsList.vue';

const page = usePage<ShopIndexPageProps>();

const selectedTab = useTabInUrl(['balls', 'evolution-items', 'tms'], 'balls');
</script>
