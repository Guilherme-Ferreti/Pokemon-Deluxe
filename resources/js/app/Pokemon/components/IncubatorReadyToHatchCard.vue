<template>
  <IncubatorCard
    :index="incubator.index"
    :status="incubator.status"
    :badge="{
      icon: IconCheck,
      text: 'Ready to hatch',
    }"
  >
    <template #picture>
      <AppPokemonEggSprite class="mx-auto" />
    </template>
    <p class="text-center text-sm text-muted-foreground">
      It's ready to hatch!
    </p>
    <template #footer>
      <Button
        type="button"
        variant="outline"
        size="sm"
        data-test="incubator-card__hatch-button"
        :disabled="form.processing"
        @click="handleHatchEgg"
      >
        Hatch
      </Button>
    </template>
  </IncubatorCard>
</template>

<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { IconCheck } from '@tabler/icons-vue';
import IncubatorCard from '@/app/Pokemon/components/IncubatorCard.vue';
import type { Incubator } from '@/app/Pokemon/types/page-props/NurseryPageProps';
import AppPokemonEggSprite from '@/shared/components/AppPokemonEggSprite.vue';
import { Button } from '@/shared/components/ui/button';
import { route } from '@/shared/types/helpers/route';

const props = defineProps<{
  incubator: Incubator;
}>();

const form = useForm();

function handleHatchEgg() {
  form.post(
    route('pokemon.web.incubators.hatch-egg', {
      incubatorIndex: props.incubator.index,
    }),
    {
      preserveScroll: true,
      only: ['incubators'],
    },
  );
}
</script>
