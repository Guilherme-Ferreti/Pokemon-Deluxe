<template>
  <AppLayout>
    <div class="grid min-h-screen bg-card lg:grid-cols-2">
      <div class="flex flex-col gap-4 p-6 md:p-10">
        <div class="flex justify-center gap-2 md:justify-start">
          <Link
            class="flex items-center gap-2 font-medium"
            :href="route('public.web.home')"
          >
            <AppLogo class="w-35" />
          </Link>
        </div>
        <main class="flex flex-1 items-center justify-center">
          <div class="w-full max-w-xs">
            <slot />
          </div>
        </main>
      </div>
      <div class="relative hidden overflow-hidden lg:block">
        <div
          class="absolute inset-0 grid grid-cols-7 place-items-center bg-muted p-6"
        >
          <img
            v-for="image in page.props.authLayout.sprites"
            :key="image"
            alt="Pokémon Deluxe"
            loading="lazy"
            :src="image"
          />
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import AppLogo from '@/shared/components/AppLogo.vue';
import AppLayout from '@/shared/layouts/AppLayout.vue';
import { route } from '@/shared/types/helpers/route';

const page = usePage();
</script>
