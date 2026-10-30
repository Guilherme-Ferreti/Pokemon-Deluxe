<template>
  <span class="inline-flex items-center gap-[0.25ch]">
    {{ name }}
    <AppPokemonGenderIcon
      v-if="gender !== PokemonGender.GENDERLESS"
      :gender="gender"
    />
  </span>
</template>

<script setup lang="ts">
import AppPokemonGenderIcon from '@/shared/components/AppPokemonGenderIcon.vue';
import { PokemonGender } from '@/shared/types/enums';
import type { Pokemon } from '@/shared/types/response';

defineProps<{
  name: Pokemon['name'];
  gender: Pokemon['gender'];
}>();
</script>
