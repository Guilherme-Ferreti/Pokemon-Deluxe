<template>
  <Head title="Your Party" />
  <AppPageHeader
    title="Your Party"
    description="The first Pokémon leads every battle. Drag to change the order."
  />
  <Alert
    v-if="page.props.isBattleOngoing"
    class="flex flex-wrap items-center justify-between gap-2"
    data-test="party-page__battle-notice"
  >
    <AlertDescription class="text-foreground">
      <span>
        <strong>You're in a battle.</strong> The party order can't change until
        it ends.
      </span>
    </AlertDescription>
    <Button as-child>
      <Link
        data-test="party-page__battle-link"
        :href="route('battle.web.show')"
      >
        Return to battle
      </Link>
    </Button>
  </Alert>
  <AppSection>
    <PartyList />
  </AppSection>
</template>

<script setup lang="ts">
import { Head, Link, setLayoutProps, usePage } from '@inertiajs/vue3';
import AppSection from '@/shared/components/content/AppSection.vue';
import AppPageHeader from '@/shared/components/layout/AppPageHeader.vue';
import { Alert, AlertDescription } from '@/shared/components/ui/alert';
import { Button } from '@/shared/components/ui/button';
import { route } from '@/shared/types/helpers/route';
import type { PartyPageProps } from '../../types/page-props/PartyPageProps';
import PartyList from './PartyList.vue';

setLayoutProps({
  breadcrumbs: [
    {
      name: 'Party',
      href: route('pokemon.web.party'),
    },
    {
      name: 'List',
    },
  ],
});

const page = usePage<PartyPageProps>();
</script>
