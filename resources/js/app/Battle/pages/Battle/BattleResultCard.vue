<template>
  <Card data-test="battle-result-card">
    <CardHeader v-if="heading">
      <CardTitle class="battle-result-card__title">
        {{ heading.title }}
      </CardTitle>
      <CardDescription>{{ heading.description }}</CardDescription>
    </CardHeader>
    <CardContent class="battle-result-card__content">
      <template v-if="gains.length > 0">
        <div
          v-for="(gain, index) in gains"
          :key="index"
          class="battle-result-card__row"
        >
          <SpeciesPortrait
            :url="gain.portrait"
            :name="gain.name"
          />
          <div class="battle-result-card__row-details">
            <div class="battle-result-card__row-summary">
              <span class="battle-result-card__name">{{ gain.name }}</span>
              <span class="battle-result-card__experience">
                +{{ gain.earnedExperience.toLocaleString('en-US') }} XP
              </span>
              <Badge v-if="gain.levelAfter > gain.levelBefore">
                Level up! {{ gain.levelBefore }} → {{ gain.levelAfter }}
              </Badge>
            </div>
            <Progress
              v-if="gain.experienceProgress !== null"
              class="battle-result-card__experience-bar"
              :model-value="gain.experienceProgress"
              :aria-label="`${gain.name} XP to the next level`"
            />
          </div>
        </div>
        <p
          v-if="page.props.battle.status === BattleStatus.LOST"
          class="battle-result-card__note"
        >
          XP is kept for the opponents you knocked out.
        </p>
      </template>
      <p
        v-else
        class="battle-result-card__note"
      >
        <IconInfoCircle
          class="size-4 shrink-0"
          aria-hidden="true"
        />
        No XP this time: the wild {{ page.props.opponent.name }} wasn't knocked
        out or caught.
      </p>
    </CardContent>
    <CardFooter>
      <Button
        as-child
        class="battle-result-card__button"
      >
        <Link :href="route('dashboard.web.home')">Back to Dashboard</Link>
      </Button>
    </CardFooter>
  </Card>
</template>

<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { IconInfoCircle } from '@tabler/icons-vue';
import { computed } from 'vue';
import SpeciesPortrait from '@/shared/components/game/SpeciesPortrait.vue';
import { Badge } from '@/shared/components/ui/badge';
import { Button } from '@/shared/components/ui/button';
import {
  Card,
  CardContent,
  CardDescription,
  CardFooter,
  CardHeader,
  CardTitle,
} from '@/shared/components/ui/card';
import { Progress } from '@/shared/components/ui/progress';
import { BattleStatus } from '@/shared/types/enums';
import { route } from '@/shared/types/helpers/route';
import type { BattleShowPageProps } from '../../types/page-props/BattleShowPageProps';

const page = usePage<BattleShowPageProps>();

type EndedStatus = Exclude<BattleStatus, BattleStatus.ONGOING>;

const gains = computed(() => page.props.result?.experienceGains ?? []);

const headings = computed<
  Record<EndedStatus, { title: string; description: string }>
>(() => ({
  [BattleStatus.WON]: {
    title: 'You won!',
    description: `The wild ${page.props.opponent.name} fainted. Your Pokémon have been healed.`,
  },
  [BattleStatus.LOST]: {
    title: 'You lost',
    description: 'Your Pokémon have been healed.',
  },
  [BattleStatus.FORFEITED]: {
    title: 'You got away safely',
    description: 'Your Pokémon have been healed.',
  },
  [BattleStatus.CAUGHT]: {
    title: `You caught the wild ${page.props.opponent.name}!`,
    description: 'Your Pokémon have been healed.',
  },
}));

const heading = computed(() =>
  page.props.battle.status === BattleStatus.ONGOING
    ? null
    : headings.value[page.props.battle.status],
);
</script>

<style scoped>
@reference '@css/app.css';

.battle-result-card__title {
  @apply text-2xl font-bold;
}

.battle-result-card__content {
  @apply flex flex-col gap-2;
}

.battle-result-card__row {
  @apply flex items-center gap-3 rounded-md border p-3;
}

.battle-result-card__row-details {
  @apply flex min-w-0 flex-1 flex-col gap-1.5;
}

.battle-result-card__row-summary {
  @apply flex flex-wrap items-center gap-1.5;
}

.battle-result-card__name {
  @apply font-semibold;
}

.battle-result-card__experience {
  @apply font-semibold text-green-700 tabular-nums dark:text-green-400;
}

.battle-result-card__experience-bar {
  @apply h-1.5;
}

.battle-result-card__note {
  @apply flex items-center gap-2 text-sm text-muted-foreground;
}

.battle-result-card__button {
  @apply w-full sm:w-auto;
}
</style>
