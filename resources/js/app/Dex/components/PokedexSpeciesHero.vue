<template>
  <Card class="@container/hero gap-0 p-0">
    <div class="flex h-full flex-col @md/hero:flex-row">
      <div
        class="flex items-center justify-center bg-muted/40 p-6 @md/hero:w-2/5"
      >
        <img
          class="size-36 object-contain"
          :src="page.props.hero.sprites.frontDefault ?? undefined"
          :alt="`${page.props.hero.name} sprite`"
        />
      </div>
      <div class="flex flex-1 flex-col gap-4 p-6">
        <p
          class="text-xs leading-none font-black tracking-widest text-muted-foreground uppercase"
          data-test="pokedex-species-hero__pokedex-number"
        >
          #{{ formatPokedexNumber(page.props.hero.pokedexNumber) }}
        </p>
        <div class="flex flex-col gap-1">
          <h1
            class="text-3xl font-bold text-foreground"
            data-test="pokedex-species-hero__name"
          >
            {{ page.props.hero.name }}
          </h1>
          <p
            class="text-base text-muted-foreground"
            data-test="pokedex-species-hero__genus"
          >
            {{ page.props.hero.genus }}
          </p>
        </div>
        <p
          class="text-muted-foreground"
          data-test="pokedex-species-hero__text-entry"
        >
          {{ page.props.hero.textEntry }}
        </p>
      </div>
    </div>
  </Card>
</template>

<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import type { PokedexShowPageProps } from '@/app/Dex/types/page-props/PokedexShowPageProps';
import { Card } from '@/shared/components/ui/card';
import { formatPokedexNumber } from '@/shared/lib/utils';

const page = usePage<PokedexShowPageProps>();
</script>
