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
          <SpeciesPortrait
            class="mr-2 inline-block"
            :url="evolution.species.portrait"
            :name="evolution.species.name"
          />
          <span class="self-center">
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
                <ItemSprite
                  v-if="evolution.evolutionItem?.sprite"
                  :name="evolution.evolutionItem.name"
                  :url="evolution.evolutionItem?.sprite"
                />
                {{ evolution.evolutionItem.name }}
              </span>
            </template>
            <span
              v-if="evolution.gender"
              class="inline-block"
            >
              &nbsp;(<PokemonGenderIcon :gender="evolution.gender" /> only)
            </span>
          </span>
        </li>
      </ul>
      <p
        v-else
        class="text-sm text-muted-foreground"
      >
        {{ section.emptyMessage }}
      </p>
      <Separator v-if="index < sections.length - 1" />
    </template>
  </AppInfoCard>
</template>

<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import type { PokedexShowPageProps } from '@/app/Dex/types/page-props/PokedexShowPageProps';
import AppInfoCard from '@/shared/components/content/AppInfoCard.vue';
import AppInfoCardSectionTitle from '@/shared/components/content/AppInfoCardSectionTitle.vue';
import ItemSprite from '@/shared/components/game/ItemSprite.vue';
import PokemonGenderIcon from '@/shared/components/game/PokemonGenderIcon.vue';
import SpeciesPortrait from '@/shared/components/game/SpeciesPortrait.vue';
import AppNavLink from '@/shared/components/layout/AppNavLink.vue';
import Separator from '@/shared/components/ui/separator/Separator.vue';
import { route } from '@/shared/types/helpers/route.js';

const page = usePage<PokedexShowPageProps>();

const sections = [
  {
    title: 'Evolves from',
    evolutions: page.props.evolutionChain.previous,
    emptyMessage: 'This pokemon does not have previous evolutions',
  },
  {
    title: 'Evolves into',
    evolutions: page.props.evolutionChain.into,
    emptyMessage: 'This pokemon does not evolve',
  },
];
</script>
