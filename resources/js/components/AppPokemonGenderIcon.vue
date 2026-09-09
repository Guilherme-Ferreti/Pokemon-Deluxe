<template>
  <IconQuestionMark
    v-if="gender == PokemonGender.GENDERLESS"
    aria-hidden="true"
    class="inline-block size-4 text-foreground"
  />
  <IconGenderMale
    v-else-if="gender == PokemonGender.MALE"
    aria-hidden="true"
    class="inline-block size-4 text-[#4DA3FF]"
  />
  <IconGenderFemale
    v-else-if="gender === PokemonGender.FEMALE"
    aria-hidden="true"
    class="inline-block size-4 text-[#FF4D6D]"
  />
  <span class="sr-only">{{ genderLabels[gender] }}</span>
</template>

<script lang="ts" setup>
import {
  IconGenderFemale,
  IconGenderMale,
  IconQuestionMark,
} from '@tabler/icons-vue';
import { PokemonGender } from '@/types';

defineProps<{
  gender: PokemonGender;
}>();

const genderLabels = {
  [PokemonGender.GENDERLESS]: 'Genderless',
  [PokemonGender.MALE]: 'Male',
  [PokemonGender.FEMALE]: 'Female',
};
</script>
