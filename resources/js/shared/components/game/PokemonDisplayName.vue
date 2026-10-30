<template>
  <span class="inline-flex items-center gap-[0.25ch]">
    {{ name }}
    <PokemonGenderIcon
      v-if="gender !== PokemonGender.GENDERLESS"
      :gender="gender"
    />
  </span>
</template>

<script setup lang="ts">
import PokemonGenderIcon from '@/shared/components/game/PokemonGenderIcon.vue';
import { PokemonGender } from '@/shared/types/enums';
import type { Pokemon } from '@/shared/types/response';

defineProps<{
  name: Pokemon['name'];
  gender: Pokemon['gender'];
}>();
</script>
