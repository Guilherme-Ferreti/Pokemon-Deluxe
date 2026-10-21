<template>
  <IconQuestionMark
    v-if="gender == PokemonGender.GENDERLESS"
    aria-hidden="true"
    class="app-pokemon-gender-icon text-foreground"
  />
  <IconGenderMale
    v-else-if="gender == PokemonGender.MALE"
    aria-hidden="true"
    class="app-pokemon-gender-icon text-[#4DA3FF]"
  />
  <IconGenderFemale
    v-else-if="gender === PokemonGender.FEMALE"
    aria-hidden="true"
    class="app-pokemon-gender-icon text-[#FF4D6D]"
  />
  <span class="sr-only">{{ $page.props.labels.pokemonGender[gender] }}</span>
</template>

<script lang="ts" setup>
import {
  IconGenderFemale,
  IconGenderMale,
  IconQuestionMark,
} from '@tabler/icons-vue';
import { PokemonGender } from '@/shared/types/enums';

defineProps<{
  gender: PokemonGender;
}>();
</script>

<style scoped>
@reference '@css/app.css';

.app-pokemon-gender-icon {
  @apply inline-block size-[1em] min-h-4 min-w-4;
}
</style>
