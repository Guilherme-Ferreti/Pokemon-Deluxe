<template>
  <Card class="party-card">
    <CardHeader>
      <CardTitle>Party</CardTitle>
    </CardHeader>
    <CardContent class="party-card__slots-wrapper">
      <Link
        v-for="pokemon in page.props.party"
        :key="pokemon.id"
        class="party-card__pokemon-slot"
        :data-test="`party-list__pokemon-${pokemon.id}-link`"
        :href="route('pokemon.web.box.show', { pokemon: pokemon.id })"
      >
        <img
          loading="lazy"
          :src="pokemon.frontSprite ?? undefined"
          :alt="pokemon.name"
        />
        <p class="party-card__pokemon-name">
          {{ pokemon.name }}
        </p>
        <p class="party-card__pokemon-level">Lv. {{ pokemon.level }}</p>
      </Link>
      <div
        v-for="i in 6 - page.props.party.length"
        :key="i"
        class="party-card__empty-slot"
      >
        <span class="party-card__empty-slot-icon"><IconPlus /></span>
      </div>
    </CardContent>
  </Card>
</template>

<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { IconPlus } from '@tabler/icons-vue';
import {
  Card,
  CardContent,
  CardHeader,
  CardTitle,
} from '@/shared/components/ui/card';
import { route } from '@/shared/types/helpers/route';
import type { PartyPageProps } from '../../types/page-props/PartyPageProps';

const page = usePage<PartyPageProps>();
</script>

<style scoped>
@reference '@css/app.css';

.party-card {
  @apply @container/card gap-3;
}

.party-card__slots-wrapper {
  @apply flex flex-wrap justify-center gap-3 @4xl/card:justify-between;
}

.party-card__pokemon-slot,
.party-card__empty-slot {
  @apply flex aspect-square w-full max-w-40 flex-col items-center justify-center rounded-lg border border-border p-2 transition-all hover:border-primary/50 hover:shadow-sm;
}

.party-card__pokemon-slot {
  @apply bg-card;
}

.party-card__pokemon-name {
  @apply text-center text-sm font-medium;
}

.party-card__pokemon-level {
  @apply text-center text-xs text-muted-foreground;
}

.party-card__empty-slot {
  @apply border-dashed bg-muted/30;
}

.party-card__empty-slot-icon {
  @apply text-muted-foreground/40;
}
</style>
