<template>
  <Head :title="species.name" />

  <p>{{ species.name }}</p>
</template>

<script setup lang="ts">
import { Head, setLayoutProps } from '@inertiajs/vue3';
import { route } from '@/types/helpers/route';

const props = defineProps<{
  species: {
    id: string;
    name: string;
    slug: string;
  };
}>();

setLayoutProps({
  breadcrumbs: [
    {
      name: 'Pokedex',
      href: route('web.dex.pokedex.index'),
    },
    {
      name: props.species.name,
      href: route('web.dex.pokedex.show', { species: props.species.slug }),
    },
    {
      name: 'View',
    },
  ],
});
</script>
