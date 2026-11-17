<template>
  <Card
    class="pokemon-fainted-card"
    role="status"
    data-test="pokemon-fainted-card"
  >
    <CardContent class="pokemon-fainted-card__content">
      <IconChevronLeft
        class="pokemon-fainted-card__icon"
        aria-hidden="true"
      />
      <div class="pokemon-fainted-card__text">
        <CardTitle
          class="pokemon-fainted-card__title"
          data-test="pokemon-fainted-card__title"
        >
          {{ page.props.player.name }} fainted!
        </CardTitle>
        <p class="pokemon-fainted-card__description">
          Pick who to send out in Your team, then press Send out. This doesn't
          use a turn.
        </p>
      </div>
    </CardContent>
  </Card>
</template>

<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { IconChevronLeft } from '@tabler/icons-vue';
import { Card, CardContent, CardTitle } from '@/shared/components/ui/card';
import type { BattleShowPageProps } from '../../types/page-props/BattleShowPageProps';

const page = usePage<BattleShowPageProps>();
</script>

<style scoped>
@reference '@css/app.css';

.pokemon-fainted-card {
  @apply animate-in duration-300 fade-in slide-in-from-bottom-2 motion-reduce:animate-none;
}

.pokemon-fainted-card__content {
  @apply flex items-center gap-3.5;
}

.pokemon-fainted-card__icon {
  @apply size-7 shrink-0 text-muted-foreground;
}

.pokemon-fainted-card__text {
  @apply flex flex-col gap-1;
}

.pokemon-fainted-card__title {
  @apply text-lg;
}

.pokemon-fainted-card__description {
  @apply text-sm text-muted-foreground;
}
</style>
