<template>
  <nav
    class="navigation__wrapper"
    aria-label="Pagination"
  >
    <Tooltip v-if="page.props.previousSpecies">
      <TooltipTrigger as-child>
        <Button
          variant="outline"
          as-child
          class="navigation__button"
          data-test="pokedex-species-navigation__previous-link"
        >
          <Link
            :aria-label="`Previous species page: ${page.props.previousSpecies.name}`"
            :href="
              route('dex.web.pokedex.show', {
                species: page.props.previousSpecies.slug,
              })
            "
          >
            <IconChevronLeft />
            <div class="navigation__text-wrapper text-right">
              <span class="navigation__pokedex-number">
                #{{
                  formatPokedexNumber(page.props.previousSpecies.pokedexNumber)
                }}
              </span>
              <span class="navigation__species-name">
                {{ page.props.previousSpecies.name }}
              </span>
            </div>
            <AppSpeciesPortrait
              :name="page.props.previousSpecies.name"
              :url="page.props.previousSpecies.portrait"
            />
          </Link>
        </Button>
      </TooltipTrigger>
      <TooltipContent>{{ page.props.previousSpecies.name }}</TooltipContent>
    </Tooltip>
    <Separator
      v-if="page.props.previousSpecies && page.props.nextSpecies"
      orientation="vertical"
      class="navigation__separator"
    />
    <Tooltip v-if="page.props.nextSpecies">
      <TooltipTrigger as-child>
        <Button
          variant="outline"
          as-child
          class="navigation__button"
          data-test="pokedex-species-navigation__next-link"
        >
          <Link
            :aria-label="`Next species page: ${page.props.nextSpecies.name}`"
            :href="
              route('dex.web.pokedex.show', {
                species: page.props.nextSpecies.slug,
              })
            "
          >
            <AppSpeciesPortrait
              :name="page.props.nextSpecies.name"
              :url="page.props.nextSpecies.portrait"
            />
            <div class="navigation__text-wrapper">
              <span class="navigation__pokedex-number">
                #{{ formatPokedexNumber(page.props.nextSpecies.pokedexNumber) }}
              </span>
              <span class="navigation__species-name">
                {{ page.props.nextSpecies.name }}
              </span>
            </div>
            <IconChevronRight />
          </Link>
        </Button>
      </TooltipTrigger>
      <TooltipContent>{{ page.props.nextSpecies.name }}</TooltipContent>
    </Tooltip>
  </nav>
</template>

<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { IconChevronLeft, IconChevronRight } from '@tabler/icons-vue';
import type { PokedexShowPageProps } from '@/app/Dex/types/page-props/PokedexShowPageProps';
import AppSpeciesPortrait from '@/shared/components/AppSpeciesPortrait.vue';
import { Button } from '@/shared/components/ui/button';
import { Separator } from '@/shared/components/ui/separator';
import {
  Tooltip,
  TooltipTrigger,
  TooltipContent,
} from '@/shared/components/ui/tooltip';
import { formatPokedexNumber } from '@/shared/lib/utils';
import { route } from '@/shared/types/helpers/route';

const page = usePage<PokedexShowPageProps>();
</script>

<style scoped>
@reference '@css/app.css';

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
