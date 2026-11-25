<template>
  <Card
    class="animate-in delay-500 duration-450 fill-mode-both fade-in slide-in-from-bottom-4 motion-reduce:animate-none"
    data-test="battle-result-card"
  >
    <CardHeader v-if="heading">
      <CardTitle
        class="text-xl font-bold sm:text-2xl"
        data-test="battle-result-card__title"
      >
        {{ heading.title }}
      </CardTitle>
      <CardDescription v-if="heading.description">
        {{ heading.description }}
      </CardDescription>
    </CardHeader>
    <CardContent class="flex flex-col gap-4">
      <section
        v-if="gains.length > 0"
        class="flex flex-col gap-2"
        aria-labelledby="battle-result-card-xp-title"
      >
        <h3
          id="battle-result-card-xp-title"
          class="text-sm font-semibold"
        >
          XP gained
        </h3>
        <ul
          class="flex flex-col gap-2"
          data-test="battle-result-card__xp-gains-list"
        >
          <li
            v-for="(gain, index) in gains"
            :key="index"
            class="flex items-center gap-3 rounded-lg border px-3 py-2.5 text-sm"
            :data-test="`battle-result-card__xp-gain-${index + 1}-item`"
          >
            <SpeciesPortrait
              class="size-10 shrink-0"
              :url="gain.portrait"
              :name="gain.name"
            />
            <span
              class="flex min-w-0 flex-1 flex-wrap items-center gap-x-1.5 gap-y-1"
            >
              <PokemonDisplayName
                class="font-semibold wrap-anywhere"
                :name="gain.name"
                :gender="gain.gender"
              />
              <span
                v-if="gain.isAtMaxLevel"
                class="text-muted-foreground"
              >
                Max level
              </span>
              <span
                v-else
                class="font-semibold text-green-700 tabular-nums dark:text-green-400"
              >
                +{{ gain.earnedExperience.toLocaleString('en-US') }} XP
              </span>
              <span
                v-if="gain.isFromExperienceShare"
                class="text-muted-foreground"
              >
                via XP Share
              </span>
              <Badge
                v-if="gain.levelAfter > gain.levelBefore"
                variant="outline"
                class="ml-auto rounded-md bg-card text-sm font-semibold"
              >
                Level up! {{ gain.levelBefore }} → {{ gain.levelAfter }}
              </Badge>
            </span>
          </li>
        </ul>
      </section>
      <p
        v-else
        class="rounded-lg bg-muted p-3 text-sm text-muted-foreground"
        data-test="battle-result-card__no-experience-empty-state"
      >
        {{ noExperienceMessage }}
      </p>
    </CardContent>
    <CardFooter class="flex flex-wrap justify-end gap-2">
      <Button
        v-if="caughtPokemon"
        as-child
        :variant="page.props.battle.area ? 'outline' : 'default'"
      >
        <Link
          data-test="battle-result-card__view-pokemon-link"
          :href="route('pokemon.web.box.index')"
        >
          View {{ caughtPokemon.name }}
        </Link>
      </Button>
      <Button
        v-else
        as-child
        :variant="page.props.battle.area ? 'outline' : 'default'"
      >
        <Link :href="route('dashboard.web.home')">Back to Dashboard</Link>
      </Button>
      <Button
        v-if="page.props.battle.area"
        type="button"
        data-test="battle-result-card__keep-exploring-button"
        :disabled="keepExploringForm.processing"
        @click="handleKeepExploring(page.props.battle.area)"
      >
        Keep exploring
      </Button>
    </CardFooter>
  </Card>
</template>

<script setup lang="ts">
import { Link, useForm, usePage } from '@inertiajs/vue3';
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
import type { Area } from '@/shared/types/enums';
import { route } from '@/shared/types/helpers/route';
import type { BattleShowPageProps } from '../../types/page-props/BattleShowPageProps';

const page = usePage<BattleShowPageProps>();

const keepExploringForm = useForm();

function handleKeepExploring(area: Area) {
  keepExploringForm.post(route('exploration.web.explore', { area }));
}

type EndedStatus = Exclude<BattleStatus, BattleStatus.ONGOING>;

const gains = computed(() => page.props.result?.experienceGains ?? []);

const opponentName = computed(() => page.props.opponent.name);

const caughtPokemon = computed(() => page.props.result?.caughtPokemon ?? null);

const caughtDescription = computed(() => {
  if (!caughtPokemon.value) {
    return null;
  }

  const { pokeballName, isInParty } = caughtPokemon.value;
  const article = /^[aeiou]/i.test(pokeballName) ? 'an' : 'a';
  const destination = isInParty
    ? 'added to your party'
    : 'sent to your Box (your party is full)';

  return `Caught with ${article} ${pokeballName} · ${destination}.`;
});

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
    description: caughtDescription.value,
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
