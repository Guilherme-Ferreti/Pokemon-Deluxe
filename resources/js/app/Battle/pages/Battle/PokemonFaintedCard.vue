<template>
  <Card
    class="animate-in duration-300 fade-in slide-in-from-bottom-2 motion-reduce:animate-none"
    role="status"
    data-test="pokemon-fainted-card"
  >
    <CardContent class="flex items-center gap-3.5">
      <IconChevronLeft
        class="size-7 shrink-0 text-muted-foreground"
        aria-hidden="true"
      />
      <div class="flex flex-col gap-1">
        <CardTitle
          class="text-lg"
          data-test="pokemon-fainted-card__title"
        >
          {{ page.props.player.name }} fainted!
        </CardTitle>
        <p class="text-sm text-muted-foreground">
          Pick who to send out in Your team, then press Send out. This doesn't
          use a turn.
        </p>
      </div>
    </CardContent>
  </Card>
</template>

<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { IconChevronLeft } from '@tabler/icons-vue';
import { Card, CardContent, CardTitle } from '@/shared/components/ui/card';
import type { BattleShowPageProps } from '../../types/page-props/BattleShowPageProps';

const page = usePage<BattleShowPageProps>();
</script>
