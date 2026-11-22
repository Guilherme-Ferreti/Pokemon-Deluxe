<template>
  <Card
    class="flex-row flex-wrap items-center justify-between gap-3 px-4 py-3"
    role="status"
  >
    <p class="text-sm">
      <span class="font-semibold">You're in a battle.</span>
      <span class="text-muted-foreground">
        Finish it before exploring again.
      </span>
    </p>
    <Button as-child>
      <Link :href="route('battle.web.show')">Return to battle</Link>
    </Button>
  </Card>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Button } from '@/shared/components/ui/button';
import { Card } from '@/shared/components/ui/card';
import { route } from '@/shared/types/helpers/route';
</script>
