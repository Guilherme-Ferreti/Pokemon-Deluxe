<template>
  <Card class="py-0">
    <AppEmptyState
      data-test="battle-in-progress-empty-state"
      title="You're in a battle"
      description="Finish your battle before exploring again."
      :icon="IconSwords"
    >
      <Button
        as-child
        size="lg"
      >
        <Link :href="route('battle.web.show')">Return to battle</Link>
      </Button>
    </AppEmptyState>
  </Card>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { IconSwords } from '@tabler/icons-vue';
import AppEmptyState from '@/shared/components/content/AppEmptyState.vue';
import { Button } from '@/shared/components/ui/button';
import { Card } from '@/shared/components/ui/card';
import { route } from '@/shared/types/helpers/route';
</script>
