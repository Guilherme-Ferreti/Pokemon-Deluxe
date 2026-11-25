<template>
  <Card
    class="gap-0 divide-y py-0"
    data-test="shop-items-list"
  >
    <slot />
  </Card>
</template>

<script setup lang="ts">
import { Card } from '@/shared/components/ui/card';
</script>
