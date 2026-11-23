<template>
  <Head />
  <div
    class="flex min-h-screen flex-col items-center justify-center bg-linear-to-b from-background to-muted px-4"
  >
    <div class="flex flex-col items-center gap-8 text-center">
      <div class="flex flex-col gap-2">
        <h1 class="text-3xl font-bold tracking-tight sm:text-5xl">
          Pokémon Deluxe
        </h1>
      </div>
      <div class="flex gap-4">
        <Button as-child>
          <Link
            :href="route('auth.web.login.create')"
            prefetch
          >
            Login
          </Link>
        </Button>
        <Button
          as-child
          variant="outline"
        >
          <Link
            :href="route('auth.web.registration.create')"
            prefetch
          >
            Sign Up
          </Link>
        </Button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/shared/components/ui/button';
import { route } from '@/shared/types/helpers/route';

defineOptions({
  layout: () => null,
});
</script>
