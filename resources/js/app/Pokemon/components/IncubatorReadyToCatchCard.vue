<template>
  <IncubatorCard
    v-if="incubator.pokemon"
    :index="incubator.index"
    :status="incubator.status"
    :badge="{
      icon: IconConfetti,
      text: 'Ready to catch',
    }"
  >
    <template #picture>
      <AppSpeciesSprite
        class="mx-auto"
        :url="incubator.pokemon.frontSprite"
        :name="incubator.pokemon.name"
      />
    </template>
    <p class="text-center text-sm text-muted-foreground">
      A
      <PokemonDisplayName
        :name="incubator.pokemon.name"
        :gender="incubator.pokemon.gender"
      />
      has hatched!
    </p>
    <template #footer>
      <Button
        type="button"
        variant="outline"
        size="sm"
        data-test="incubator-card__catch-button"
        @click="$emit('catchPokemon')"
      >
        Catch
      </Button>
    </template>
  </IncubatorCard>
</template>

<script setup lang="ts">
import { IconConfetti } from '@tabler/icons-vue';
import IncubatorCard from '@/app/Pokemon/components/IncubatorCard.vue';
import type { Incubator } from '@/app/Pokemon/types/page-props/NurseryPageProps';
import AppSpeciesSprite from '@/shared/components/AppSpeciesSprite.vue';
import PokemonDisplayName from '@/shared/components/PokemonDisplayName.vue';
import { Button } from '@/shared/components/ui/button';

defineProps<{
  incubator: Incubator;
}>();

defineEmits<{
  catchPokemon: [];
}>();
</script>
