<template>
  <Card
    class="battle-options-card"
    data-test="battle-options-card"
  >
    <CardHeader>
      <CardTitle>More options</CardTitle>
    </CardHeader>
    <CardContent class="battle-options-card__content">
      <BattleForfeitButton />
    </CardContent>
  </Card>
</template>

<script setup lang="ts">
import {
  Card,
  CardContent,
  CardHeader,
  CardTitle,
} from '@/shared/components/ui/card';
import BattleForfeitButton from './BattleForfeitButton.vue';
</script>

<style scoped>
@reference '@css/app.css';

.battle-options-card__content {
  @apply flex flex-wrap gap-2;
}
</style>
