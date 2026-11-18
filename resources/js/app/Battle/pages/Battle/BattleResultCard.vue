<template>
  <Card
    class="battle-result-card"
    data-test="battle-result-card"
  >
    <CardHeader v-if="heading">
      <CardTitle
        class="battle-result-card__title"
        data-test="battle-result-card__title"
      >
        {{ heading.title }}
      </CardTitle>
      <CardDescription v-if="heading.description">
        {{ heading.description }}
      </CardDescription>
    </CardHeader>
    <CardContent class="battle-result-card__content">
      <section
        v-if="gains.length > 0"
        class="battle-result-card__section"
        aria-labelledby="battle-result-card-xp-title"
      >
        <h3
          id="battle-result-card-xp-title"
          class="battle-result-card__section-title"
        >
          XP gained
        </h3>
        <ul
          class="battle-result-card__xp-gains"
          data-test="battle-result-card__xp-gains-list"
        >
          <li
            v-for="(gain, index) in gains"
            :key="index"
            class="battle-result-card__xp-gain"
            :data-test="`battle-result-card__xp-gain-${index + 1}-item`"
          >
            <SpeciesPortrait
              class="battle-result-card__portrait"
              :url="gain.portrait"
              :name="gain.name"
            />
            <span class="battle-result-card__xp-gain-details">
              <PokemonDisplayName
                class="battle-result-card__name"
                :name="gain.name"
                :gender="gain.gender"
              />
              <span
                v-if="gain.isAtMaxLevel"
                class="battle-result-card__max-level"
              >
                Max level
              </span>
              <span
                v-else
                class="battle-result-card__experience"
              >
                +{{ gain.earnedExperience.toLocaleString('en-US') }} XP
              </span>
              <span
                v-if="gain.isFromExperienceShare"
                class="battle-result-card__experience-share"
              >
                via XP Share
              </span>
              <Badge
                v-if="gain.levelAfter > gain.levelBefore"
                variant="outline"
                class="battle-result-card__level-up"
              >
                Level up! {{ gain.levelBefore }} → {{ gain.levelAfter }}
              </Badge>
            </span>
          </li>
        </ul>
      </section>
      <p
        v-else
        class="battle-result-card__no-experience"
        data-test="battle-result-card__no-experience-empty-state"
      >
        {{ noExperienceMessage }}
      </p>
    </CardContent>
    <CardFooter class="battle-result-card__actions">
      <Button as-child>
        <Link :href="route('dashboard.web.home')">Back to Dashboard</Link>
      </Button>
    </CardFooter>
  </Card>
</template>

<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import PokemonDisplayName from '@/shared/components/game/PokemonDisplayName.vue';
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
import { BattleStatus } from '@/shared/types/enums';
import { route } from '@/shared/types/helpers/route';
import type { BattleShowPageProps } from '../../types/page-props/BattleShowPageProps';

const page = usePage<BattleShowPageProps>();

type EndedStatus = Exclude<BattleStatus, BattleStatus.ONGOING>;

const gains = computed(() => page.props.result?.experienceGains ?? []);

const opponentName = computed(() => page.props.opponent.name);

const headings = computed<
  Record<EndedStatus, { title: string; description: string | null }>
>(() => ({
  [BattleStatus.WON]: {
    title: 'You won!',
    description: `The wild ${opponentName.value} fainted.`,
  },
  [BattleStatus.LOST]: {
    title: `You lost to the wild ${opponentName.value}`,
    description: null,
  },
  [BattleStatus.FORFEITED]: {
    title: 'You got away safely',
    description: null,
  },
  [BattleStatus.CAUGHT]: {
    title: `Gotcha! ${opponentName.value} was caught`,
    description: null,
  },
}));

const heading = computed(() =>
  page.props.battle.status === BattleStatus.ONGOING
    ? null
    : headings.value[page.props.battle.status],
);

const noExperienceMessage = computed(() =>
  page.props.battle.status === BattleStatus.LOST
    ? `No XP this time: the wild ${opponentName.value} wasn't knocked out.`
    : `No XP this time: the wild ${opponentName.value} wasn't knocked out or caught.`,
);
</script>

<style scoped>
@reference '@css/app.css';

.battle-result-card {
  @apply animate-in delay-500 duration-450 fill-mode-both fade-in slide-in-from-bottom-4 motion-reduce:animate-none;
}

.battle-result-card__title {
  @apply text-xl font-bold sm:text-2xl;
}

.battle-result-card__content {
  @apply flex flex-col gap-4;
}

.battle-result-card__section {
  @apply flex flex-col gap-2;
}

.battle-result-card__section-title {
  @apply text-sm font-semibold;
}

.battle-result-card__xp-gains {
  @apply flex flex-col gap-2;
}

.battle-result-card__xp-gain {
  @apply flex items-center gap-3 rounded-lg border px-3 py-2.5 text-sm;
}

.battle-result-card__portrait {
  @apply size-10 shrink-0;
}

.battle-result-card__xp-gain-details {
  @apply flex min-w-0 flex-1 flex-wrap items-center gap-x-1.5 gap-y-1;
}

.battle-result-card__name {
  @apply font-semibold wrap-anywhere;
}

.battle-result-card__experience {
  @apply font-semibold text-green-700 tabular-nums dark:text-green-400;
}

.battle-result-card__max-level {
  @apply text-muted-foreground;
}

.battle-result-card__experience-share {
  @apply text-muted-foreground;
}

.battle-result-card__level-up {
  @apply ml-auto rounded-md bg-card text-sm font-semibold;
}

.battle-result-card__no-experience {
  @apply rounded-lg bg-muted p-3 text-sm text-muted-foreground;
}

.battle-result-card__actions {
  @apply flex flex-wrap justify-end gap-2;
}
</style>
