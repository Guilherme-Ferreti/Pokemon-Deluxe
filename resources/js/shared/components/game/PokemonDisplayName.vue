<template>
  <span>
    {{ leadingWords
    }}<span class="whitespace-nowrap"
      >{{ lastWord }}
      <PokemonGenderIcon
        v-if="gender !== PokemonGender.GENDERLESS"
        :gender="gender"
      />
    </span>
  </span>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import PokemonGenderIcon from '@/shared/components/game/PokemonGenderIcon.vue';
import { PokemonGender } from '@/shared/types/enums';
import type { Pokemon } from '@/shared/types/response';

const props = defineProps<{
  name: Pokemon['name'];
  gender: Pokemon['gender'];
}>();

const leadingWords = computed(() =>
  props.name.slice(0, props.name.lastIndexOf(' ') + 1),
);

const lastWord = computed(() =>
  props.name.slice(props.name.lastIndexOf(' ') + 1),
);
</script>
