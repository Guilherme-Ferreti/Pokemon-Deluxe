<template>
  <AppInfoCard title="Evolutions">
    <template
      v-for="(section, index) in sections"
      :key="section.title"
    >
      <AppInfoCardSectionTitle :title="section.title" />
      <ul
        v-if="section.evolutions.length > 0"
        class="list-inside text-sm"
      >
        <li
          v-for="evolution in section.evolutions"
          :key="evolution.species.name"
          class="mb-2 grid grid-cols-[auto_1fr]"
        >
          <AppSpeciesPortrait
            class="mr-2 inline-block"
            :url="evolution.species.portrait"
            :name="evolution.species.name"
          />
          <div class="self-center">
            <AppNavLink
              class="font-medium underline"
              :url="
                route('dex.web.pokedex.show', {
                  species: evolution.species.slug,
                })
              "
              :title="evolution.species.name"
            />
            <template v-if="evolution.minLevel">
              at level {{ evolution.minLevel }}
            </template>
            <template v-if="evolution.evolutionItem">
              using
              <span class="inline-block">
                <AppItemSprite
                  v-if="evolution.evolutionItem?.sprite"
                  :name="evolution.evolutionItem.name"
                  :url="evolution.evolutionItem?.sprite"
                />
                {{ evolution.evolutionItem.name }}
              </span>
            </template>
          </div>
        </li>
      </ul>
      <p
        v-else
        class="text-sm"
      >
        {{ section.emptyMessage }}
      </p>
      <Separator v-if="index < sections.length - 1" />
    </template>
  </AppInfoCard>
</template>

<script setup lang="ts">
import AppInfoCard from '@/components/AppInfoCard.vue';
import AppInfoCardSectionTitle from '@/components/AppInfoCardSectionTitle.vue';
import AppItemSprite from '@/components/AppItemSprite.vue';
import AppNavLink from '@/components/AppNavLink.vue';
import AppSpeciesPortrait from '@/components/AppSpeciesPortrait.vue';
import Separator from '@/components/ui/separator/Separator.vue';
import type { DexPokedexShowPageProps } from '@/pages/Dex/Pokedex/Show.vue';
import { route } from '@/types/helpers/route.js';

const props = defineProps<{
  evolutionChain: DexPokedexShowPageProps['evolutionChain'];
}>();

const sections = [
  {
    title: 'Evolves from',
    evolutions: props.evolutionChain.previous,
    emptyMessage: 'This pokemon does not have previous evolutions.',
  },
  {
    title: 'Evolves into',
    evolutions: props.evolutionChain.into,
    emptyMessage: 'This pokemon does not evolve.',
  },
];
</script>
