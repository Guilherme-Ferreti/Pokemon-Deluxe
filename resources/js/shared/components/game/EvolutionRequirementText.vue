<template>
  <span>
    <template v-if="minLevel">at level {{ minLevel }}</template>
    <template v-if="evolutionItem">
      using
      <span
        class="inline-block"
        :data-test="itemTestId"
      >
        <ItemSprite
          v-if="evolutionItem.sprite"
          class="size-6"
          :name="evolutionItem.name"
          :url="evolutionItem.sprite"
        />
        {{ evolutionItem.name }}
      </span>
    </template>
    <span
      v-if="gender"
      class="inline-block"
    >
      &nbsp;(<PokemonGenderIcon :gender="gender" /> only)
    </span>
  </span>
</template>

<script setup lang="ts">
import ItemSprite from '@/shared/components/game/ItemSprite.vue';
import PokemonGenderIcon from '@/shared/components/game/PokemonGenderIcon.vue';
import type { PokemonGender } from '@/shared/types/enums';

defineProps<{
  minLevel: number | null;
  evolutionItem: { name: string; sprite: string | null } | null;
  gender: PokemonGender | null;
  itemTestId?: string;
}>();
</script>
