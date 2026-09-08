<template>
  <nav
    class="navigation__wrapper"
    aria-label="Pagination"
  >
    <Tooltip v-if="previousSpecies">
      <TooltipTrigger as-child>
        <Button
          variant="outline"
          as-child
          class="navigation__button"
          data-test="pokedex-species-navigation__previous-link"
        >
          <Link
            :aria-label="`Previous species page: ${previousSpecies.name}`"
            :href="
              route('dex.web.pokedex.show', { species: previousSpecies.slug })
            "
          >
            <IconChevronLeft />
            <div class="navigation__text-wrapper text-right">
              <span class="navigation__pokedex-number">
                #{{ formatPokedexNumber(previousSpecies.pokedexNumber) }}
              </span>
              <span class="navigation__species-name">
                {{ previousSpecies.name }}
              </span>
            </div>
            <AppSpeciesPortrait
              :name="previousSpecies.name"
              :url="previousSpecies.portrait"
            />
          </Link>
        </Button>
      </TooltipTrigger>
      <TooltipContent>{{ previousSpecies.name }}</TooltipContent>
    </Tooltip>
    <Separator
      v-if="previousSpecies && nextSpecies"
      orientation="vertical"
      class="navigation__separator"
    />
    <Tooltip v-if="nextSpecies">
      <TooltipTrigger as-child>
        <Button
          variant="outline"
          as-child
          class="navigation__button"
          data-test="pokedex-species-navigation__next-link"
        >
          <Link
            :aria-label="`Next species page: ${nextSpecies.name}`"
            :href="route('dex.web.pokedex.show', { species: nextSpecies.slug })"
          >
            <AppSpeciesPortrait
              :name="nextSpecies.name"
              :url="nextSpecies.portrait"
            />
            <div class="navigation__text-wrapper">
              <span class="navigation__pokedex-number">
                #{{ formatPokedexNumber(nextSpecies.pokedexNumber) }}
              </span>
              <span class="navigation__species-name">
                {{ nextSpecies.name }}
              </span>
            </div>
            <IconChevronRight />
          </Link>
        </Button>
      </TooltipTrigger>
      <TooltipContent>{{ nextSpecies.name }}</TooltipContent>
    </Tooltip>
  </nav>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { IconChevronLeft, IconChevronRight } from '@tabler/icons-vue';
import AppSpeciesPortrait from '@/components/AppSpeciesPortrait.vue';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import {
  Tooltip,
  TooltipTrigger,
  TooltipContent,
} from '@/components/ui/tooltip';
import { formatPokedexNumber } from '@/lib/utils';
import type { DexPokedexShowPageProps } from '@/pages/Dex/Pokedex/Show.vue';
import { route } from '@/types/helpers/route';

defineProps<{
  previousSpecies?: DexPokedexShowPageProps['previousSpecies'];
  nextSpecies?: DexPokedexShowPageProps['nextSpecies'];
}>();
</script>

<style scoped>
@reference '../../css/app.css';

.navigation__wrapper {
  @apply flex h-13.5 items-center gap-4;
}

.navigation__button {
  @apply h-auto p-2;
}

.navigation__text-wrapper {
  @apply hidden flex-col gap-1 @sm:flex;
}

.navigation__pokedex-number {
  @apply text-[9px] font-black tracking-widest text-slate-400;
}

.navigation__species-name {
  @apply w-[10ch] truncate text-xs font-bold;
}
</style>
