<template>
  <section
    class="team-rail-group"
    :aria-labelledby="headingId"
  >
    <h3
      :id="headingId"
      class="team-rail-group__heading"
    >
      {{ heading }}
    </h3>
    <ul
      v-if="isList"
      class="team-rail-group__list"
    >
      <slot />
    </ul>
    <slot v-else />
  </section>
</template>

<script setup lang="ts">
import { useId } from 'vue';

withDefaults(
  defineProps<{
    heading: string;
    isList?: boolean;
  }>(),
  {
    isList: true,
  },
);

const headingId = useId();
</script>

<style scoped>
@reference '@css/app.css';

.team-rail-group {
  @apply flex flex-col gap-1.5;
}

.team-rail-group__heading {
  @apply text-xxs font-bold tracking-wider text-muted-foreground uppercase;
}

/* The same 1 to 3 columns as the bench's radio group (`BattleTeamRadioGroup`), one list in the wide layout. */
.team-rail-group__list {
  @apply grid grid-cols-1 gap-1.5 @md:grid-cols-2 @2xl:grid-cols-3 @5xl:flex @5xl:flex-col;
}
</style>
