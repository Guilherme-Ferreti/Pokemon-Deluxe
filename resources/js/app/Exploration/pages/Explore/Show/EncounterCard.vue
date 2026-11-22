<template>
  <Card
    v-if="encounter"
    class="mx-auto w-full max-w-md items-center gap-4 px-6 text-center"
    :class="{
      'border-amber-400 ring-1 ring-amber-400 dark:border-amber-500 dark:ring-amber-500':
        isRare,
    }"
    :data-test="isRare ? 'encounter-card--rare' : 'encounter-card'"
  >
    <SpeciesSprite
      class="size-40"
      :url="encounter.sprite"
      :name="encounter.name"
    />
    <div class="flex flex-col items-center gap-1.5">
      <div
        v-if="isRare"
        class="flex flex-wrap justify-center gap-1"
      >
        <Badge
          v-if="encounter.isShiny"
          variant="outline"
          class="border-amber-300 bg-amber-100 text-amber-800 dark:border-amber-700 dark:bg-amber-950 dark:text-amber-300"
        >
          <IconStarFilled />
          Shiny
        </Badge>
        <Badge
          v-if="rarityLabel"
          variant="outline"
          class="border-amber-300 bg-amber-100 text-amber-800 dark:border-amber-700 dark:bg-amber-950 dark:text-amber-300"
        >
          {{ rarityLabel }}
        </Badge>
      </div>
      <h2
        class="text-xl font-bold"
        data-test="encounter-card__title"
      >
        A wild {{ encounter.name }} appeared!
      </h2>
      <div class="flex flex-wrap items-center justify-center gap-1.5">
        <PokemonGenderIcon
          v-if="encounter.gender !== PokemonGender.GENDERLESS"
          :gender="encounter.gender"
        />
        <Badge
          variant="outline"
          class="tabular-nums"
        >
          Lv {{ encounter.level }}
        </Badge>
        <PokemonTypeBadges :types="encounter.types" />
      </div>
      <p
        v-if="rarityLabel"
        class="text-sm text-muted-foreground"
      >
        {{ rarityLabel }} Pokémon are much harder to catch. Weaken it and put it
        to sleep first.
      </p>
    </div>
    <div class="flex flex-wrap justify-center gap-2">
      <Button
        type="button"
        size="lg"
        data-test="encounter-card__battle-button"
        :disabled="form.processing"
        @click="handleBattle"
      >
        Battle
      </Button>
      <Button
        type="button"
        variant="outline"
        size="lg"
        data-test="encounter-card__keep-exploring-button"
        :disabled="form.processing"
        @click="handleKeepExploring"
      >
        Keep exploring
      </Button>
    </div>
  </Card>
</template>

<script setup lang="ts">
import { useForm, usePage } from '@inertiajs/vue3';
import { IconStarFilled } from '@tabler/icons-vue';
import { computed } from 'vue';
import PokemonGenderIcon from '@/shared/components/game/PokemonGenderIcon.vue';
import PokemonTypeBadges from '@/shared/components/game/PokemonTypeBadges.vue';
import SpeciesSprite from '@/shared/components/game/SpeciesSprite.vue';
import { Badge } from '@/shared/components/ui/badge';
import { Button } from '@/shared/components/ui/button';
import { Card } from '@/shared/components/ui/card';
import { PokemonGender } from '@/shared/types/enums';
import { route } from '@/shared/types/helpers/route';
import type { ExploreShowPageProps } from '../../../types/page-props/ExploreShowPageProps';

const page = usePage<ExploreShowPageProps>();

const form = useForm();

const encounter = computed(() => page.props.encounter);

const rarityLabel = computed(() => {
  if (encounter.value?.isMythical) {
    return 'Mythical';
  }

  return encounter.value?.isLegendary ? 'Legendary' : null;
});

const isRare = computed(
  () => encounter.value?.isShiny === true || rarityLabel.value !== null,
);

function handleBattle() {
  form.post(route('exploration.web.battle'));
}

function handleKeepExploring() {
  form.post(route('exploration.web.explore', { area: page.props.area.slug }));
}
</script>
