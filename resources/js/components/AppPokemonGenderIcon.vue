<template>
  <IconQuestionMark
    v-if="gender == PokemonGender.GENDERLESS"
    aria-hidden="true"
    class="inline-block size-4 align-text-bottom text-foreground"
  />
  <IconGenderMale
    v-else-if="gender == PokemonGender.MALE"
    aria-hidden="true"
    class="inline-block size-4 align-text-bottom text-[#4DA3FF]"
  />
  <IconGenderFemale
    v-else-if="gender === PokemonGender.FEMALE"
    aria-hidden="true"
    class="inline-block size-4 align-text-bottom text-[#FF4D6D]"
  />
  <span class="sr-only">{{ $page.props.labels.pokemonGender[gender] }}</span>
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
</script>
