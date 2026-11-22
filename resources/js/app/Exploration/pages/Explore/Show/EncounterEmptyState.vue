<template>
  <Card class="py-0">
    <AppEmptyState
      data-test="encounter-empty-state"
      title="Nothing here yet"
      :icon="IconSearch"
      :description="`Explore the ${page.props.area.label} to find a wild Pokémon.`"
    >
      <Button
        type="button"
        size="lg"
        data-test="encounter-empty-state__explore-button"
        :disabled="form.processing"
        @click="handleExplore"
      >
        Explore
      </Button>
    </AppEmptyState>
  </Card>
</template>

<script setup lang="ts">
import { useForm, usePage } from '@inertiajs/vue3';
import { IconSearch } from '@tabler/icons-vue';
import AppEmptyState from '@/shared/components/content/AppEmptyState.vue';
import { Button } from '@/shared/components/ui/button';
import { Card } from '@/shared/components/ui/card';
import { route } from '@/shared/types/helpers/route';
import type { ExploreShowPageProps } from '../../../types/page-props/ExploreShowPageProps';

const page = usePage<ExploreShowPageProps>();

const form = useForm();

function handleExplore() {
  form.post(route('exploration.web.explore', { area: page.props.area.slug }));
}
</script>
