<template>
  <div class="navigation__wrapper">
    <Link
      v-if="previousSpecies"
      :href="route('web.dex.pokedex.show', { species: previousSpecies.slug })"
    >
      <Card class="navigation__card">
        <AppSpeciesPortrait
          :name="previousSpecies.name"
          :url="previousSpecies.portrait"
        />
        <div class="navigation__text-wrapper">
          <span class="navigation__pokedex-number">
            #{{ formatPokedexNumber(previousSpecies.pokedexNumber) }}
          </span>
          <span class="navigation__species-name">
            {{ previousSpecies.name }}
          </span>
        </div>
      </Card>
    </Link>
    <Separator
      v-if="previousSpecies && nextSpecies"
      orientation="vertical"
    />
    <Link
      v-if="nextSpecies"
      :href="route('web.dex.pokedex.show', { species: nextSpecies.slug })"
    >
      <Card class="navigation__card flex-row-reverse">
        <div class="navigation__text-wrapper">
          <span class="navigation__pokedex-number text-right">
            #{{ formatPokedexNumber(nextSpecies.pokedexNumber) }}
          </span>
          <span class="navigation__species-name">
            {{ nextSpecies.name }}
          </span>
        </div>
        <AppSpeciesPortrait
          :name="nextSpecies.name"
          :url="nextSpecies.portrait"
        />
      </Card>
    </Link>
  </div>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppSpeciesPortrait from '@/components/AppSpeciesPortrait.vue';
import { Card } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import type { DexPokedexShowPageProps } from '@/pages/Dex/Pokedex/Show.vue';
import { route } from '@/types/helpers/route';

defineProps<{
  previousSpecies?: DexPokedexShowPageProps['previousSpecies'];
  nextSpecies?: DexPokedexShowPageProps['nextSpecies'];
}>();

function formatPokedexNumber(pokedexNumber: number): string {
  return pokedexNumber.toString().padStart(4, '0');
}
</script>

<style scoped>
@reference '../../css/app.css';

.navigation__wrapper {
  @apply flex h-13 items-center space-x-4;
}

.navigation__card {
  @apply flex-row items-center gap-2 p-3;
}

.navigation__text-wrapper {
  @apply flex flex-col gap-1;
}

.navigation__pokedex-number {
  @apply mb-1 text-[9px] leading-none font-black tracking-widest text-slate-400 uppercase;
}

.navigation__species-name {
  @apply text-xs leading-none font-bold text-slate-700 capitalize dark:text-slate-200;
}
</style>
