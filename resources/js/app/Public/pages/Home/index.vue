<template>
  <Head>
    <meta
      head-key="description"
      name="description"
      :content="description"
    />
  </Head>
  <div class="flex min-h-screen flex-col">
    <header class="border-b bg-background">
      <div
        class="mx-auto flex max-w-7xl flex-wrap items-center gap-x-6 gap-y-3 px-4 py-3.5 sm:px-6"
      >
        <Link
          class="flex items-center gap-2.5 text-lg font-bold text-foreground"
          :href="route('public.web.home')"
        >
          <img
            src="/assets/logo-icon.png"
            alt=""
            class="size-7"
          />
          Pokémon Deluxe
        </Link>
        <nav
          class="ml-auto flex flex-wrap gap-2"
          aria-label="Account"
        >
          <Button
            as-child
            size="lg"
            variant="ghost"
          >
            <Link
              data-test="home__header-login-link"
              :href="route('auth.web.login.create')"
              prefetch
            >
              Log in
            </Link>
          </Button>
          <Button
            as-child
            size="lg"
            variant="outline"
          >
            <Link
              data-test="home__header-registration-link"
              :href="route('auth.web.registration.create')"
              prefetch
            >
              Create an account
            </Link>
          </Button>
        </nav>
      </div>
    </header>
    <main class="flex-1">
      <section aria-labelledby="hero-title">
        <div
          class="mx-auto flex max-w-7xl flex-wrap items-center gap-8 px-4 py-12 sm:gap-12 sm:px-6 sm:py-20"
        >
          <div class="flex min-w-0 flex-[1_1_20rem] flex-col gap-5">
            <h1
              id="hero-title"
              class="text-4xl leading-none font-bold tracking-tight sm:text-5xl"
            >
              Pokémon Deluxe
            </h1>
            <p class="max-w-md text-lg leading-relaxed text-muted-foreground">
              {{ description }}
            </p>
            <div class="flex flex-wrap gap-3">
              <Button
                as-child
                size="lg"
              >
                <Link
                  data-test="home__hero-registration-link"
                  :href="route('auth.web.registration.create')"
                  prefetch
                >
                  Create an account
                </Link>
              </Button>
              <Button
                as-child
                size="lg"
                variant="outline"
              >
                <Link
                  data-test="home__hero-login-link"
                  :href="route('auth.web.login.create')"
                  prefetch
                >
                  Log in
                </Link>
              </Button>
            </div>
          </div>
          <img
            src="/assets/home/battle.webp"
            width="1464"
            height="800"
            alt="A battle in Pokémon Deluxe: Shiny Charizard against a wild Arceus, with your team on the left, the moves below and the battle log on the right"
            class="h-auto w-full max-w-175 min-w-0 flex-[2_1_35rem] rounded-2xl border shadow-xl"
          />
        </div>
      </section>
      <div class="border-y bg-background">
        <AppSection class="mx-auto max-w-6xl px-4 py-14 sm:px-6">
          <AppSectionHeading
            id="features-title"
            title="What you can do"
          />
          <ul
            class="grid grid-cols-[repeat(auto-fill,minmax(14rem,1fr))] gap-3"
            aria-labelledby="features-title"
            data-test="home__feature-list"
          >
            <li
              v-for="feature in features"
              :key="feature.label"
            >
              <Card class="h-full flex-row items-center gap-3 px-4 py-3.5">
                <span
                  class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-secondary text-primary"
                >
                  <component
                    :is="feature.icon"
                    class="size-5"
                    aria-hidden="true"
                  />
                </span>
                <span class="font-medium">{{ feature.label }}</span>
              </Card>
            </li>
          </ul>
        </AppSection>
      </div>
      <AppSection class="mx-auto max-w-6xl px-4 pt-10 pb-2 sm:px-6">
        <AppSectionHeading
          id="credits-title"
          title="Credits"
        />
        <ul
          class="grid grid-cols-[repeat(auto-fill,minmax(17rem,1fr))] gap-3"
          aria-labelledby="credits-title"
        >
          <li>
            <Card class="h-full gap-1 p-4 text-sm leading-normal">
              <a
                class="font-semibold text-primary hover:underline"
                href="https://pokeapi.co/"
              >
                PokéAPI
              </a>
              <span class="text-muted-foreground">
                Pokémon, move and type data.
              </span>
            </Card>
          </li>
          <li>
            <Card class="h-full gap-1 p-4 text-sm leading-normal">
              <a
                class="font-semibold text-primary hover:underline"
                href="https://sprites.pmdcollab.org/"
              >
                PMD Sprite Repository
              </a>
              <span class="text-muted-foreground">
                Portraits and sprites by the PMDCollab artists.
                <a
                  class="text-primary underline"
                  href="https://github.com/PMDCollab/SpriteCollab/blob/master/credit_names.txt"
                >
                  See every contributor</a
                >.
              </span>
            </Card>
          </li>
        </ul>
      </AppSection>
    </main>
    <footer class="mx-auto w-full max-w-6xl px-4 py-6 sm:px-6">
      <p class="text-sm text-muted-foreground">
        A fan-made game, not affiliated with Nintendo, Game Freak or The Pokémon
        Company.
      </p>
    </footer>
  </div>
</template>

<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
  IconArrowsExchange,
  IconBuildingCastle,
  IconChecklist,
  IconEgg,
  IconMap,
  IconTrophy,
} from '@tabler/icons-vue';
import AppSection from '@/shared/components/content/AppSection.vue';
import AppSectionHeading from '@/shared/components/content/AppSectionHeading.vue';
import { Button } from '@/shared/components/ui/button';
import { Card } from '@/shared/components/ui/card';
import { route } from '@/shared/types/helpers/route';

defineOptions({
  layout: () => null,
});

const description =
  'A Pokémon game you play in your browser. Catch Pokémon, battle trainers and gyms, and trade with other players.';

const features = [
  { icon: IconMap, label: 'Explore areas and catch Pokémon' },
  { icon: IconTrophy, label: 'Battle trainers and gyms' },
  { icon: IconBuildingCastle, label: 'Climb the Battle Tower' },
  { icon: IconArrowsExchange, label: 'Trade with other players' },
  { icon: IconEgg, label: 'Hatch eggs' },
  { icon: IconChecklist, label: 'Complete daily missions' },
];
</script>
